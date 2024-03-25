<?php

namespace App\Tests\Product\MessageHandler;

use App\Entity\Product\Supplier;
use App\Message\Product\MercurialImport;
use App\Message\Product\ProductUpdate;
use App\MessageHandler\Product\MercurialImportHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
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
        $validatorMock->expects($this->exactly(3))->method('validate')->willReturn(new ConstraintViolationList([]));

        $repository = $this->entityManager->getRepository(Supplier::class);
        $supplier = $repository->findOneByName('Primeur Deluxe');

        $handler = new MercurialImportHandler(messageBus: $messageBusMock, logger: $loggerMock, validator: $validatorMock);
        $message = new MercurialImport(filename: self::CSV_EXAMPLE_LOCATION, supplierId: $supplier->getId());

        $messageBusMock
            ->expects(self::exactly(count: 3))
            ->method('dispatch')
            ->with(self::isInstanceOf(className: ProductUpdate::class))
            ->willReturn(new Envelope(message:$message));

        $handler(mercurialImportMessage: $message);
    }

    protected function tearDown(): void
    {
        $this->entityManager = null;
    }
}
