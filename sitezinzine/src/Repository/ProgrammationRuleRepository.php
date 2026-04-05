<?php

namespace App\Repository;

use App\Entity\ProgrammationRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProgrammationRule>
 */
class ProgrammationRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProgrammationRule::class);
    }

    public function save(ProgrammationRule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProgrammationRule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function createNotDeletedQueryBuilder(string $alias = 'r')
    {
        return $this->createQueryBuilder($alias)
            ->andWhere(sprintf('%s.deletedAt IS NULL', $alias));
    }

    public function createActiveQueryBuilder(string $alias = 'r')
    {
        return $this->createNotDeletedQueryBuilder($alias)
            ->andWhere(sprintf('%s.isActive = :active', $alias))
            ->setParameter('active', true);
    }

    public function findAllNotDeleted(): array
    {
        return $this->createNotDeletedQueryBuilder('r')
            ->leftJoin('r.category', 'c')
            ->addSelect('c')
            ->orderBy('c.titre', 'ASC')
            ->addOrderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findDeletedRules(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.category', 'c')
            ->addSelect('c')
            ->andWhere('r.deletedAt IS NOT NULL')
            ->orderBy('r.deletedAt', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveRules(): array
    {
        return $this->createActiveQueryBuilder('r')
            ->leftJoin('r.category', 'c')
            ->addSelect('c')
            ->orderBy('c.titre', 'ASC')
            ->addOrderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveRulesForDate(\DateTimeImmutable $date): array
    {
        return $this->createActiveQueryBuilder('r')
            ->leftJoin('r.category', 'c')
            ->addSelect('c')
            ->andWhere('(r.validFrom IS NULL OR r.validFrom <= :date)')
            ->andWhere('(r.validUntil IS NULL OR r.validUntil >= :date)')
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('c.titre', 'ASC')
            ->addOrderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}