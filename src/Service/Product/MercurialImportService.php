<?php

namespace App\Service\Product;


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
}
