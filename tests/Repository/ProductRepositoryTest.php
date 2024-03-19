<?php

use App\Entity\Product;
use App\Entity\Supplier;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductRepositoryTest extends KernelTestCase
{
    private ?EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testGetProductsByCode()
    {
        /** @var ProductRepository $repo */
        $repo = $this->entityManager->getRepository(className: Product::class);
        $products = $repo->findByCode(code: 'tom01');

        $this->assertCount(expectedCount: 2, haystack: $products, message: 'check that the number of products is correct');

        $supplierNames = [];
        foreach ($products as $product) {
            $this->assertInstanceOf(expected: Product::class, actual: $product, message: 'check that objects are instances of product');
            $supplierNames[] = $product->getSupplier()->getName();
        }

        $this->assertNotEquals(expected: $supplierNames[0], actual: $supplierNames[1], message: 'Get 2 products with the same code from different suppliers');
    }

    public function testCreateProductWithExistingCodeException()
    {
        /** @var ProductRepository $repo */
        $repo = $this->entityManager->getRepository(className: Product::class);
        $mozzarellaProduct = $repo->findOneBy(criteria: ['code' => 'moz01']);
        $supplier = $mozzarellaProduct->getSupplier();

        $product = new Product();
        $product->setCode('moz01');
        $product->setSupplier(supplier: $supplier);
        $product->setDescription(description: 'mozza bio');
        $product->setPrice(price: 1.5);

        $this->expectException(exception: UniqueConstraintViolationException::class);
        $this->entityManager->persist(object: $product);
        $this->entityManager->flush();

        $product->setCode('moz02');
        $this->entityManager->persist(object: $product);
        $this->entityManager->flush();

        $createdProduct = $repo->findOneBy(criteria: ['code' => 'moz02']);
        $this->assertInstanceOf(expected: Product::class, actual: $createdProduct, message: 'Created Product Successfully by changing the code');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
