<?php

namespace App\MessageHandler\Product;
use App\Entity\Product\Product;
use App\Entity\Product\Supplier;
use App\Message\Product\ProductUpdate;
use App\Repository\Product\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsMessageHandler]
class ProductUpdateHandler
{
    /** @var ProductRepository */
    private EntityRepository $productRepository;

    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
    ) {
        $this->productRepository = $this->em->getRepository(className: Product::class);
    }

    public function __invoke(ProductUpdate $productUpdate): void
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

        $errors = $this->validator->validate($product);

        if (0 === count($errors)) {
            if (true === $shouldPersist) {
                $this->em->persist($product);
            }
            $this->em->flush();
        }
    }
}
