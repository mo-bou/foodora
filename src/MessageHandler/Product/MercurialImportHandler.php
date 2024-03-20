<?php

namespace App\MessageHandler\Product;

use App\Entity\Supplier;
use App\Message\Product\MercurialImport;
use App\Message\Product\ProductUpdate;
use App\Repository\SupplierRepository;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class MercurialImportHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {
    }
    public function __invoke(MercurialImport $mercurialImportMessage)
    {
        $supplierId = $mercurialImportMessage->getSupplierId();
        $csvFilepath = $mercurialImportMessage->getFilename();
        $this->logger->info('opening '.$csvFilepath);
        $csvRawData = file_get_contents(filename: $csvFilepath);
        $csvData = preg_split("/\r\n|\n|\r/", $csvRawData);

        foreach ($csvData as $csvRow) {
            $this->logger->info('product csv row : '. $csvRow);
            $productData = explode(separator: ',', string: $csvRow);

            $productUpdateMessage = new ProductUpdate(
                description: $productData[0],
                code : $productData[1],
                price: $productData[2],
                supplierId: $supplierId,
            );

            $this->messageBus->dispatch(message: $productUpdateMessage);
        }

    }
}
