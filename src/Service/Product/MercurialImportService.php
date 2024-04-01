<?php

namespace App\Service\Product;


use App\Entity\Product\Mercurial;
use App\Message\Product\ProductUpdate;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\String\Slugger\SluggerInterface;

class MercurialImportService
{
    private string $mercurialDirectory;
    private SluggerInterface $slugger;

    public function __construct(string $mercurialDirectory, SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
        $this->mercurialDirectory = $mercurialDirectory;

        if (false === file_exists(filename: $this->mercurialDirectory)) {
            mkdir(directory: $this->mercurialDirectory, permissions: 0755, recursive: true);
        }
    }


    public function storeMercurialFileContents(string $fileContents, string $supplierName): string
    {
        $filename = $this->slugger->slug($supplierName).'_'.uniqid().'.csv';
        $filePath = $this->mercurialDirectory.'/'.$filename;
        file_put_contents(filename: $filePath, data: $fileContents);

        return $filename;
    }

    public function getMercurialFileContents(string $filename): string
    {
        if (false === file_exists($this->mercurialDirectory.'/'. $filename)) {
            throw new FileNotFoundException($filename);
        }

        return file_get_contents(filename: $this->mercurialDirectory.'/'.$filename);
    }

    /**
     * @param Mercurial $mercurial
     * @return array<int, ProductUpdate>
     */
    public function getProductUpdateMessagesFromMercurial(Mercurial $mercurial): array
    {
        $rawData = $this->getMercurialFileContents(filename: $mercurial->getFilename());
        $csvData = preg_split(pattern: "/\r\n|\n|\r/", subject: $rawData);

        $messages = [];
        foreach ($csvData as $csvRow) {
            if (true === empty(trim($csvRow))) {
                continue;
            }

            $productData = explode(separator: ',', string: $csvRow);
            $messages[] =  new ProductUpdate(
                description: $productData[0],
                code : $productData[1],
                price: (float) $productData[2],
                supplierId: $mercurial->getSupplier()->getId(),
            );
        }

        return $messages;
    }
}
