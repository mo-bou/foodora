<?php

namespace App\MessageHandler\Product;

use App\Message\Product\MercurialImport;
use App\Message\Product\ProductUpdate;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsMessageHandler]
class MercurialImportHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private ValidatorInterface $validator,
    ) {
    }
    public function __invoke(MercurialImport $mercurialImportMessage): void
    {
        $supplierId = $mercurialImportMessage->getSupplierId();
        $csvFilepath = $mercurialImportMessage->getFilename();
        $csvRawData = file_get_contents(filename: $csvFilepath);
        $csvData = preg_split("/\r\n|\n|\r/", $csvRawData);

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

            $errors = $this->validator->validate($productUpdateMessage);

            if (0 === count($errors)) {
                $this->messageBus->dispatch(message: $productUpdateMessage);
            } else {
                //TODO : Implement the desired behavior for invalid messages
                $this->logger->error(sprintf('invalid Product data : %s', $errors->__toString()));
            }
        }
    }
}
