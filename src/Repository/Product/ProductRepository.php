<?php

namespace App\Repository\Product;

use App\Entity\Product\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    const PRODUCT_MAX_RESULTS = 20;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByCode(string $code)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.code = :code')
            ->setParameter('code', $code)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(self::PRODUCT_MAX_RESULTS)
            ->getQuery()
            ->getResult();
    }

    public function findOneByCodeAndSupplierId(string $code, int $supplierId): ?Product
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.code) = LOWER(:code)')
            ->setParameter('code', $code, ParameterType::STRING)
            ->andWhere('p.supplier = :supplierId')
            ->setParameter('supplierId', $supplierId, ParameterType::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByCodeAndSupplierName(string $code, string $supplierName): ?Product
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.supplier', 's')
            ->where('ILIKE(p.code, :code) = true')
            ->andWhere('ILIKE(s.name, :supplierName) = true')
            ->setParameter('code', $code)
            ->setParameter('supplierName', $supplierName);

        return $qb
                ->getQuery()
                ->getOneOrNullResult();
    }

    public function findByCodeOrDescriptionContainingString(string $searchString, int $limit = 0, int $offset = 0)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('ILIKE(p.code, :searchString) = true')
            ->orWhere('ILIKE(p.description, :searchString) = true')
            ->setParameter('searchString', '%'.$searchString.'%')
            ->setFirstResult(firstResult: $offset);
        if (0 < $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
