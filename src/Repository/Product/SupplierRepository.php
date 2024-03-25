<?php

namespace App\Repository\Product;

use App\Entity\Product\Supplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Supplier>
 *
 * @method Supplier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Supplier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Supplier[]    findAll()
 * @method Supplier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupplierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Supplier::class);
    }

    public function findOneByName(string $name): ?Supplier
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('LOWER(s.name) = LOWER(:name)')
            ->setParameter(key: 'name', value: $name, type: ParameterType::STRING);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, mixed> $orderBy
     * @param int $limit
     * @param int $offset
     * @return iterable<int, Supplier>
     */
    public function findByCaseInsensitive(array $criteria, array $orderBy = [], $limit = null, $offset = null): iterable
    {
        $qb = $this->createQueryBuilder('s');
        foreach ($criteria as $propertyName => $propertyValue) {
            $parameterName = 'prop_'.$propertyName;
            $qb->andWhere('LOWER(s.'.$propertyName.') = LOWER(:'.$parameterName.')')
                ->setParameter(key: $parameterName, value: $propertyValue);
        }

        if (false === empty($orderBy)){
            foreach ($orderBy as $property => $order) {
                $qb->addOrderBy(sort: $property, order: $order);
            }
        }

        if (null !== $limit) {
            $qb->setMaxResults(maxResults: $limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult(firstResult: $offset);
        }

        return $qb->getQuery()->getResult();
    }
}
