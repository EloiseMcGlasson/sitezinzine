<?php

namespace App\Repository;

use App\Entity\Emission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emission>
 */
class EmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emission::class);
    }

    //    /**
    //     * @return Emission[] Returns an array of Emission objects
    //     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.url != :val')
            ->setParameter('val', $value)
            ->orderBy('e.datepub', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param integer $duree
     * @return Emission[]
     */
    public function findWithDureeLowerThan(int $duree): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.duree < :duree')
            ->orderBy('r.duree','ASC')
            ->setParameter('duree', $duree)
            ->getQuery()
            ->getResult();

    }
    //    public function findOneBySomeField($value): ?Emission
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
