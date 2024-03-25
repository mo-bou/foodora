<?php

namespace App\Message\Product;

use Symfony\Component\Validator\Constraints;

class ProductUpdate
{
    public function __construct(
        private string $description,
        #[Constraints\Length(min: 1, max: 6)]
        private string $code,
        #[Constraints\LessThan(1000), Constraints\GreaterThan(0)]
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
