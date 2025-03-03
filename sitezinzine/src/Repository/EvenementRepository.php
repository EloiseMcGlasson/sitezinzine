<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * @return Evenement[] Returns an array of Evenement objects
     */
    public function findUpcomingEvenements(): array
{
    $qb = $this->createQueryBuilder('a')
        ->where('a.dateDebut >= :today')  // Événements futurs
        ->orWhere('(:today BETWEEN a.dateDebut AND a.dateFin)') // Événements en cours
        ->andWhere('a.valid = 1')
        ->setParameter('today', new \DateTimeImmutable('today'))
        ->orderBy('a.dateDebut', 'ASC');

    return $qb->getQuery()->getResult();
}


    public function findAllDesc() : array {
        return $this->createQueryBuilder('a')
            ->where('a.softDelete = 0')
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
        
    }

    public function findOldEvenements(\DateTimeImmutable $dateLimit): array
{
    return $this->createQueryBuilder('a')
        ->where('a.dateFin < :dateLimit')
        ->setParameter('dateLimit', $dateLimit)
        ->getQuery()
        ->getResult();
}

    //    /**
    //     * @return Evenement[] Returns an array of Evenement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Evenement
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
