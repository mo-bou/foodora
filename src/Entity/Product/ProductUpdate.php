<?php

namespace App\Entity\Product;

use App\Repository\Product\ProductUpdateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductUpdateRepository::class)]
class ProductUpdate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 25, options: ['default' => 'created'])]
    private string $status;

    /** @var array<string, mixed> $data */
    #[ORM\Column]
    private array $data = [];

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Supplier $supplier = null;

    public function validate(): void
    {
        $this->status = 'validated';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): ProductUpdate
    {
        $this->id = $id;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): ProductUpdate
    {
        $this->status = $status;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     * @return $this
     */
    public function setData(array $data): ProductUpdate
    {
        $this->data = $data;
        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): ProductUpdate
    {
        $this->supplier = $supplier;
        return $this;
    }
}
