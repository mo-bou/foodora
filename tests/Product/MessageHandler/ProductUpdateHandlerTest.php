<?php

namespace App\Tests\Product\MessageHandler;

use App\Entity\Product\Product;
use App\Entity\Product\Supplier;
use App\Message\Product\ProductUpdate;
use App\MessageHandler\Product\ProductUpdateHandler;
use App\Repository\Product\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductUpdateHandlerTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;

    private MessageBusInterface $messageBus;

    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        $this->entityManager = $container->get(id: 'doctrine')->getManager();
        $this->validator = Validation::createValidator();
    }

    public function testProductUpdateHandler(): void
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->entityManager->getRepository(Product::class);

        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->exactly(2))->method('validate')->willReturn(new ConstraintViolationList([]));

        $supplier = new Supplier('supplier_test');
        $this->entityManager->persist($supplier);
        $this->entityManager->flush();

        //Testing that the message was correctly handled and that a new product was inserted
        $productCreateMessage = new ProductUpdate(
            description: 'test 1',
            code: 'a01',
            price: 1.5,
            supplierId: $supplier->getId()
        );

        //add a new product
        $productUpdateHandler = new ProductUpdateHandler(em: $this->entityManager, validator: $validatorMock);
        $productUpdateHandler(productUpdate: $productCreateMessage);

        //Checking that the product was actually created
        $createdProduct = $productRepository->findOneByCodeAndSupplierId(code: 'a01', supplierId: $supplier->getId());
        $this->assertInstanceOf(expected: Product::class, actual: $createdProduct);

        $productUpdateMessage = new ProductUpdate(
            description: 'tomato potato',
            code: 'a01',
            price: 5,
            supplierId: $supplier->getId()
        );
        //Updating an existing product
        $productUpdateHandler(productUpdate: $productUpdateMessage);
        $updatedProduct = $productRepository->findOneByCodeAndSupplierId(code: 'a01', supplierId: $supplier->getId());

        $this->assertEquals(expected: 5, actual: $updatedProduct->getPrice());
        $this->assertEquals(expected: 'tomato potato', actual: $updatedProduct->getDescription());
        $this->assertEquals(expected: $createdProduct->getId(), actual: $updatedProduct->getId());
    }

    protected function tearDown(): void
    {
        $this->entityManager = null;
    }
}
