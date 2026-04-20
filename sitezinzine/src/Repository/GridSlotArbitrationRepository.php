<?php

namespace App\Repository;

use App\Entity\GridSlotArbitration;
use App\Entity\ProgrammationRuleSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GridSlotArbitrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GridSlotArbitration::class);
    }

    public function findOneBySlotAndStartsAt(
        ProgrammationRuleSlot $slot,
        \DateTimeImmutable $startsAt
    ): ?GridSlotArbitration {
        return $this->createQueryBuilder('a')
            ->andWhere('a.slot = :slot')
            ->andWhere('a.startsAt = :startsAt')
            ->setParameter('slot', $slot)
            ->setParameter('startsAt', $startsAt)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPendingForWeek(
        \DateTimeImmutable $weekStart,
        \DateTimeImmutable $weekEnd
    ): array {
        return $this->createQueryBuilder('a')
            ->andWhere('a.startsAt >= :weekStart')
            ->andWhere('a.startsAt < :weekEnd')
            ->andWhere('a.status = :status')
            ->setParameter('weekStart', $weekStart)
            ->setParameter('weekEnd', $weekEnd)
            ->setParameter('status', GridSlotArbitration::STATUS_PENDING)
            ->orderBy('a.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findToReschedule(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.needsReschedule = :needsReschedule')
            ->andWhere('a.rescheduleStatus = :status')
            ->setParameter('needsReschedule', true)
            ->setParameter('status', GridSlotArbitration::RESCHEDULE_STATUS_PENDING)
            ->orderBy('a.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}