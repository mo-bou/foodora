<?php

namespace MessageHandler;

use App\Entity\Product;
use App\Entity\Supplier;
use App\Message\Product\ProductUpdate;
use App\MessageHandler\Product\ProductUpdateHandler;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductUpdateHandlerTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;

    private MessageBusInterface $messageBus;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        $this->entityManager = $container->get(id: 'doctrine')->getManager();
    }

    public function testProductUpdateHandler(): void
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->entityManager->getRepository(Product::class);

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
        $productUpdateHandler = new ProductUpdateHandler(em: $this->entityManager);
        $productUpdateHandler(productUpdate: $productCreateMessage);

        $createProduct = $productRepository->findOneByCodeAndSupplierId(code: 'a01', supplierId: $supplier->getId());
        $this->assertInstanceOf(expected: Product::class, actual: $createProduct);

        //Testing that the message was correctly handled and that the product previously created was updated
        $productUpdateMessage = new ProductUpdate(
            description: 'tomato potato',
            code: 'a01',
            price: 5,
            supplierId: $supplier->getId()
        );
        $productUpdateHandler(productUpdate: $productUpdateMessage);
        $updatedProduct = $productRepository->findOneByCodeAndSupplierId(code: 'a01', supplierId: $supplier->getId());

        $this->assertEquals(expected: 5, actual: $updatedProduct->getPrice());
        $this->assertEquals(expected: 'tomato potato', actual: $updatedProduct->getDescription());
        $this->assertEquals(expected: $createProduct->getId(), actual: $updatedProduct->getId());
    }

    protected function tearDown(): void
    {
        $this->entityManager = null;
    }
}
