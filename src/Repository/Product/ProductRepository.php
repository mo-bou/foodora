<?php

namespace App\Repository\Product;

use App\Entity\Product\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
    const PRODUCT_MAX_RESULTS = 18;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param string $code
     * @return iterable<int, Product>
     */
    public function findByCode(string $code): iterable
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

    /**
     * @param string $searchString
     * @param int $limit
     * @param int $page
     * @return Paginator<Product>
     */
    public function findByCodeOrDescriptionContainingString(string $searchString, int $limit = 0, int $page = 1): Paginator
    {
        if (0 === $limit) {
            $limit = self::PRODUCT_MAX_RESULTS;
        }

        $qb = $this->createPaginatedQueryBuilder('p', $limit, $page)
            ->where('ILIKE(p.code, :searchString) = true')
            ->orWhere('ILIKE(p.description, :searchString) = true')
            ->setParameter(key: 'searchString', value: '%'.$searchString.'%', type: ParameterType::STRING);

        return new Paginator($qb, fetchJoinCollection: false);
    }

    /**
     * @param int $limit
     * @param int $page
     * @return Paginator<Product>
     */
    public function findAllPaginated(int $limit = 0, int $page = 1): Paginator
    {
        $qb = $this->createPaginatedQueryBuilder('p', $limit, $page);

        return new Paginator($qb, fetchJoinCollection: false);
    }

    public function createPaginatedQueryBuilder(string $alias, int $limit = 0, int $page = 1): QueryBuilder
    {
        return $this->createQueryBuilder(alias: $alias)
            ->setFirstResult(firstResult: ($page - 1) * $limit)
            ->setMaxResults(maxResults: $limit);
    }
}
