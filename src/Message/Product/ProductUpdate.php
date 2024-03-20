<?php

namespace App\Message\Product;

class ProductUpdate
{
    public function __construct(
        private string $description,
        private string $code,
        private float $price,
        private int $supplierId
    ) {
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getSupplierId(): int
    {
        return $this->supplierId;
    }


}
