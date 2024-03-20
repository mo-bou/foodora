<?php

namespace App\Message\Product;

class MercurialImport
{
    public function __construct(
        private string $filename,
        private int $supplierId,
    ) {
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getSupplierId(): int
    {
        return $this->supplierId;
    }
}
