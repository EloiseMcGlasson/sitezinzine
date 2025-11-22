<?php

namespace App\Repository;

use App\Entity\Diffusion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Diffusion>
 */
class DiffusionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Diffusion::class);
    }


    public function findLatest(int $limit = 10): array
{
    return $this->createQueryBuilder('d')
        ->orderBy('d.id', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}

public function findByWeek(\DateTimeInterface $start, \DateTimeInterface $end): array
{
    return $this->createQueryBuilder('d')
    ->addSelect('e')
    ->join('d.emission', 'e')
    ->andWhere('d.horaireDiffusion >= :start')
    ->andWhere('d.horaireDiffusion < :end')
    ->setParameter('start', $start)
    ->setParameter('end', $end)
    ->orderBy('d.horaireDiffusion', 'ASC')
    ->getQuery()
    ->getResult();

}


}