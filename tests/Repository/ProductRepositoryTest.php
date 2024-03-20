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

    private ?ProductRepository $productRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->productRepository = $this->entityManager->getRepository(className: Product::class);
    }

    public function testGetProductsByCode()
    {
        $products = $this->productRepository->findByCode(code: 'tom01');

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
        $mozzarellaProduct = $this->productRepository->findOneBy(criteria: ['code' => 'moz01']);
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

        $createdProduct = $this->productRepository->findOneBy(criteria: ['code' => 'moz02']);
        $this->assertInstanceOf(expected: Product::class, actual: $createdProduct, message: 'Created Product Successfully by changing the code');
    }

    public function testFindOneByCodeAndSupplierName()
    {
        $product = $this->productRepository->findOneByCodeAndSupplierName(code: 'tom01', supplierName: 'Primeur Deluxe');
        $this->assertInstanceOf(expected: Product::class, actual: $product);
    }

    public function testFindOneByCodeAndSupplierNameWithWrongCase()
    {
        /** @var ProductRepository $repo */
        $repo = $this->entityManager->getRepository(className: Product::class);
        $product = $repo->findOneByCodeAndSupplierName(code: 'TOM01', supplierName: 'primeur deluxe');
        $this->assertInstanceOf(expected: Product::class, actual: $product);
    }

    public function testFindOneByCodeAndSupplierId()
    {
        /** @var Supplier $supplier */
        $supplier = $this->entityManager->getRepository(className: Supplier::class)->findOneBy(['name' => 'Primeur Deluxe']);
        $productCode = $supplier->getProducts()[0]->getCode();
        $product = $this->productRepository->findOneByCodeAndSupplierId(code: $productCode, supplierId: $supplier->getId());
        $this->assertInstanceOf(expected: Product::class, actual: $product);

        $product = $this->productRepository->findOneByCodeAndSupplierId(code: mb_strtoupper($productCode), supplierId: $supplier->getId());
        $this->assertInstanceOf(expected: Product::class, actual: $product);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
        $this->productRepository = null;
    }
}
