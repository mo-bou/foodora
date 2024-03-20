<?php

namespace App\MessageHandler\Product;
use App\Entity\Product;
use App\Entity\Supplier;
use App\Message\Product\ProductUpdate;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProductUpdateHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(ProductUpdate $productUpdate)
    {
        $supplierId = $productUpdate->getSupplierId();
        $product = $this->productRepository->findOneByCodeAndSupplierId(code: $productUpdate->getCode(), supplierId: $supplierId);
        $shouldPersist = false;
        if (null === $product) {
            $product = new Product();
            $shouldPersist = true;
        }
        $product
            ->setSupplier(supplier: $this->em->getReference(entityName: Supplier::class, id: $supplierId))
            ->setPrice(price: $productUpdate->getPrice())
            ->setCode(code: $productUpdate->getCode())
            ->setDescription(description: $productUpdate->getDescription());

        if (true === $shouldPersist) {
            $this->em->persist($product);
        }
        $this->em->flush();
    }
}
