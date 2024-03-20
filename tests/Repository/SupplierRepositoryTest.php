<?php

use App\Entity\Supplier;
use App\Repository\SupplierRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SupplierRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;


    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get(id: 'doctrine')
            ->getManager();

    }

    public function testGetSuppliers(): void
    {
        /** @var SupplierRepository $repo */
        $repo = $this->entityManager->getRepository(className: Supplier::class);
        $suppliers = $repo->findAll();

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
        /** @var SupplierRepository $repo */
        $repo = $this->entityManager->getRepository(className: Supplier::class);

        $this->entityManager->persist(new Supplier(name: 'Gogevi'));
        $this->entityManager->flush();

        $supplier = $repo->findOneByName(name: 'Gogevi');
        $this->assertEquals(expected: 'Gogevi', actual: $supplier->getName());

        $supplier = $repo->findOneByName(name: 'gogevi');
        $this->assertEquals(expected: 'Gogevi', actual: $supplier->getName());
    }

    public function testFindByCaseInsentive(): void
    {
        /** @var SupplierRepository $repo */
        $repo = $this->entityManager->getRepository(className: Supplier::class);

        $this->entityManager->persist(new Supplier(name: 'Gogevi'));
        $this->entityManager->flush();

        $suppliers = $repo->findByCaseInsensitive(['name' => 'gogevi']);
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
    }
}
