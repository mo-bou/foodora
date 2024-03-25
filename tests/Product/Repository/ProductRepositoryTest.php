<?php

namespace App\Tests\Product\Repository;

use App\Entity\Product\Product;
use App\Entity\Product\Supplier;
use App\Repository\Product\ProductRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function testGetProductsByCode(): void
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

    public function testCreateProductWithExistingCodeException(): void
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

    public function testFindOneByCodeAndSupplierName(): void
    {
        $product = $this->productRepository->findOneByCodeAndSupplierName(code: 'tom01', supplierName: 'Primeur Deluxe');
        $this->assertInstanceOf(expected: Product::class, actual: $product);
    }

    public function testFindOneByCodeAndSupplierNameWithWrongCase(): void
    {
        /** @var ProductRepository $repo */
        $repo = $this->entityManager->getRepository(className: Product::class);
        $product = $repo->findOneByCodeAndSupplierName(code: 'TOM01', supplierName: 'primeur deluxe');
        $this->assertInstanceOf(expected: Product::class, actual: $product);
    }

    public function testFindOneByCodeAndSupplierId(): void
    {
        /** @var Supplier $supplier */
        $supplier = $this->entityManager->getRepository(className: Supplier::class)->findOneBy(['name' => 'Primeur Deluxe']);
        $productCode = $supplier->getProducts()[0]->getCode();
        $product = $this->productRepository->findOneByCodeAndSupplierId(code: $productCode, supplierId: $supplier->getId());
        $this->assertInstanceOf(expected: Product::class, actual: $product);

        $product = $this->productRepository->findOneByCodeAndSupplierId(code: mb_strtoupper($productCode), supplierId: $supplier->getId());
        $this->assertInstanceOf(expected: Product::class, actual: $product);
    }

    public function testFindByCodeOrDescriptionContainingString(): void
    {
        $products = $this->productRepository->findByCodeOrDescriptionContainingString(searchString: 'tom');
        $this->assertCount(expectedCount: 3, haystack: $products);
        $lowercaseSearchProducts = (array) $products->getIterator();
        $productIdsWithLowerCaseSearch = array_map(callback: function (Product $product) {
            return $product->getId();
        }, array: $lowercaseSearchProducts);

        $products2 = $this->productRepository->findByCodeOrDescriptionContainingString(searchString: 'TOM');
        $this->assertCount(expectedCount: 3, haystack: $products2);
        $uppercaseSearchProduct = (array) $products2->getIterator();
        $productIdsWithUpperCaseSearch = array_map(callback: function (Product $product) {
            return $product->getId();
        }, array: $uppercaseSearchProduct);

        $this->assertEquals(expected: 0, actual: count(array_diff($productIdsWithLowerCaseSearch, $productIdsWithUpperCaseSearch)));;
    }

    public function testProductPagination(): void
    {
        $firstPageResult = $this->productRepository->findByCodeOrDescriptionContainingString(searchString: 'tom', limit: 1)->getIterator();
        $secondPageResult = $this->productRepository->findByCodeOrDescriptionContainingString(searchString: 'tom', limit: 1, page: 2)->getIterator();
        $thirdPageResult = $this->productRepository->findByCodeOrDescriptionContainingString(searchString: 'tom', limit: 1, page: 3)->getIterator();
        $fourthPageResult = $this->productRepository->findByCodeOrDescriptionContainingString(searchString: 'tom', limit: 1, page: 4)->getIterator();

        $this->assertCount(1, $firstPageResult);
        $this->assertCount(1, $thirdPageResult);
        $this->assertCount(0, $fourthPageResult);
        $this->assertNotEquals($firstPageResult[0]->getId(), $secondPageResult[0]->getId());
        $this->assertNotEquals($secondPageResult[0]->getId(), $thirdPageResult[0]->getId());
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
