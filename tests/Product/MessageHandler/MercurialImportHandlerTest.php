<?php

namespace App\Tests\Product\MessageHandler;

use App\Entity\Product\Mercurial;
use App\Entity\Product\Supplier;
use App\Message\Product\MercurialImport;
use App\Message\Product\ProductUpdate;
use App\MessageHandler\Product\MercurialImportHandler;
use App\Repository\Product\SupplierRepository;
use App\Service\Product\MercurialImportService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MercurialImportHandlerTest extends KernelTestCase
{
    const CSV_EXAMPLE_CONTENTS = <<<CSV
Poire Non EU Colis de 10kg Bio Gros,1512,18.79
Aubergine Italie Barquette de 2 pièces Conventionnel Gros,4314,1.29
Aubergine EU Colis de 10kg Bio Petit,4976,17.57
CSV;

    const CSV_EXAMPLE_LOCATION = '/tmp/test.csv';

    private ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        file_put_contents(filename: '/tmp/test.csv', data: self::CSV_EXAMPLE_CONTENTS);

        $container = $kernel->getContainer();
        $this->entityManager = $container->get(id: 'doctrine')->getManager();
    }

    public function testMercurialImportHandler(): void
    {
        $messageBusMock = $this->createMock(originalClassName: MessageBusInterface::class);
        $loggerMock = $this->createMock(originalClassName: LoggerInterface::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $mailerMock = $this->createMock(MailerInterface::class);
        $sluggerMock = $this->createMock(SluggerInterface::class);

        $parameterBag = new ParameterBag();
        $parameterBag->set('app.report.mail_to', 'test@example.org');
        $parameterBag->set('dev.email', 'test@example.com');

        $validatorMock
            ->expects($this->exactly(3))
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        /** @var SupplierRepository $repository */
        $repository = $this->entityManager->getRepository(Supplier::class);
        $supplier = $repository->findOneByName('Primeur Deluxe');

        $handler = new MercurialImportHandler(
            messageBus: $messageBusMock,
            logger: $loggerMock,
            validator: $validatorMock,
            entityManager: $this->entityManager,
            parameterBag: $parameterBag,
            mailer: $mailerMock,
            mercurialImportService: new MercurialImportService(mercurialDirectory: '/tmp', slugger: $sluggerMock)
        );
        $message = new MercurialImport(filename: 'test.csv', supplierId: $supplier->getId());

        $messageBusMock
            ->expects(self::exactly(count: 3))
            ->method('dispatch')
            ->with(self::isInstanceOf(className: ProductUpdate::class))
            ->willReturn(new Envelope(message:$message));

        $mailerMock
            ->expects($this->once())
            ->method('send');

        $handler(mercurialImportMessage: $message);

        /** @var Mercurial $mercurialEntity */
        $mercurialEntity = $this->entityManager->getRepository(Mercurial::class)->findOneBy(['supplier'=> $supplier->getId()]);
        $this->assertEquals($mercurialEntity->getFilename(), $message->getFilename());
    }

    protected function tearDown(): void
    {
    }
}
