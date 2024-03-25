<?php

namespace App\Repository\Product;

use App\Entity\Product\Mercurial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mercurial>
 *
 * @method Mercurial|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mercurial|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mercurial[]    findAll()
 * @method Mercurial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MercurialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mercurial::class);
    }

    //    /**
    //     * @return Mercurial[] Returns an array of Mercurial objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Mercurial
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
