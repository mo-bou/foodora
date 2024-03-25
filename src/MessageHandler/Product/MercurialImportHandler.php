<?php

namespace App\MessageHandler\Product;

use App\Entity\Product\Mercurial;
use App\Entity\Product\Supplier;
use App\Message\Product\MercurialImport;
use App\Message\Product\ProductUpdate;
use App\Service\Product\MercurialImportService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsMessageHandler]
class MercurialImportHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private MailerInterface $mailer,
        private MercurialImportService $mercurialImportService,
    ) {
    }
    public function __invoke(MercurialImport $mercurialImportMessage): void
    {
        $supplierId = $mercurialImportMessage->getSupplierId();
        $mercurial = new Mercurial();
        $mercurial->setFilename($mercurialImportMessage->getFilename());
        $mercurial->setSupplier($this->entityManager->getReference(Supplier::class, $supplierId));
        $this->entityManager->persist($mercurial);
        $this->entityManager->flush();

        $csvRawData = $this->mercurialImportService->getMercurialFileContents($mercurialImportMessage->getFilename());
        $csvData = preg_split("/\r\n|\n|\r/", $csvRawData);

        $nbInvalidRows = 0;
        $nbDispatchedRows = 0;
        $productUpdatesStatus = [];
        foreach ($csvData as $csvRow) {
            if (true === empty(trim($csvRow))) {
                continue;
            }
            $productData = explode(separator: ',', string: $csvRow);
            $productUpdateMessage = new ProductUpdate(
                description: $productData[0],
                code : $productData[1],
                price: (float) $productData[2],
                supplierId: $supplierId,
            );

            /** @var ConstraintViolationList $errors*/
            $errors = $this->validator->validate($productUpdateMessage);

            if (0 === count($errors)) {
                $nbDispatchedRows++;
                $this->messageBus->dispatch(message: $productUpdateMessage);
                $productUpdatesStatus[] = [
                    'code' => $productUpdateMessage->getCode(),
                    'status' => 'dispatched',
                ];
            } else {
                $nbInvalidRows++;
                $productUpdatesStatus[] = [
                    'code' => $productUpdateMessage->getCode(),
                    'status' => 'failed',
                    'errors' => $errors,
                ];
            }
        }
        $this->sendReport($mercurialImportMessage, $nbDispatchedRows, $nbInvalidRows, $productUpdatesStatus);
    }

    /**
     * @param MercurialImport $mercurialImportMessage
     * @param int $nbDispatchedRows
     * @param int $nbInvalidRows
     * @param array<int, array{'code': string, 'status': string, 'errors'?:ConstraintViolationList}> $productUpdatesStatus
     * @return void
     */
    private function sendReport(MercurialImport $mercurialImportMessage, int $nbDispatchedRows, int $nbInvalidRows, array $productUpdatesStatus): void
    {
        $supplier = $this->entityManager->getRepository(Supplier::class)->findOneBy(['id' => $mercurialImportMessage->getSupplierId()]);

        $email = new TemplatedEmail();
        $email
            ->from(new Address(address: $this->parameterBag->get('dev.email'), name: 'foodmarket'))
            ->to(new Address(address: $this->parameterBag->get('app.report.mail_to')))
            ->subject(subject: 'Mercurial Import Report : '.$mercurialImportMessage->getFilename())
            ->htmlTemplate('mail/reporting/product/mercurial-import.html.twig')
            ->context([
                'supplier' => $supplier,
                'file' => $mercurialImportMessage->getFilename(),
                'nb_dispatched' => $nbDispatchedRows,
                'nb_invalid' => $nbInvalidRows,
                'product_updates_status' => $productUpdatesStatus
            ]);

        try {
            $this->mailer->send(message: $email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
