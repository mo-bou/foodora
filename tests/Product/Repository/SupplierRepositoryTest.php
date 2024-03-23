<?php

namespace App\Tests\Product\Repository;

use App\Entity\Product\Supplier;
use App\Repository\Product\SupplierRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SupplierRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?SupplierRepository $repository;


    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get(id: 'doctrine')
            ->getManager();

        $this->repository = $this->entityManager->getRepository(className: Supplier::class);
    }

    public function testGetSuppliers(): void
    {
        $suppliers = $this->repository->findAll();

        $this->assertCount(expectedCount: 2, haystack: $suppliers);
    }

    public function testCreateSupplier(): void
    {
        $supplier = new Supplier(name: 'Gogevi');
        $this->entityManager->persist($supplier);
        $this->entityManager->flush();

        $this->assertIsNumeric($supplier->getId());
    }

    public function testSupplierNameIsUnique(): void
    {
        $supplier = new Supplier(name: 'Gogevi');
        $this->entityManager->persist($supplier);
        $this->entityManager->flush();
        $this->assertIsNumeric($supplier->getId());

        $supplierSameName = new Supplier(name: 'Gogevi');
        $this->expectException(UniqueConstraintViolationException::class);
        $this->entityManager->persist($supplierSameName);
        $this->entityManager->flush();
    }

    public function testFindOneByName(): void
    {
        $this->entityManager->persist(new Supplier(name: 'Gogevi'));
        $this->entityManager->flush();

        $supplier = $this->repository->findOneByName(name: 'Gogevi');
        $this->assertEquals(expected: 'Gogevi', actual: $supplier->getName());

        $supplier = $this->repository->findOneByName(name: 'gogevi');
        $this->assertEquals(expected: 'Gogevi', actual: $supplier->getName());
    }

    public function testFindByCaseInsentive(): void
    {
        $this->entityManager->persist(new Supplier(name: 'Gogevi'));
        $this->entityManager->flush();

        $suppliers = $this->repository->findByCaseInsensitive(['name' => 'gogevi']);
        foreach ($suppliers as $supplier) {
            $this->assertEquals('gogevi', mb_strtolower($supplier->getName()));
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repository = null;
    }
}
