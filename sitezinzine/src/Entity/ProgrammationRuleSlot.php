<?php

namespace App\Entity;

use App\Repository\ProgrammationRuleSlotRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProgrammationRuleSlotRepository::class)]
#[ORM\Table(name: 'programmation_rule_slot')]
#[ORM\Index(columns: ['day_of_week'], name: 'idx_programmation_rule_slot_day')]
#[ORM\Index(columns: ['broadcast_rank'], name: 'idx_programmation_rule_slot_rank')]
#[ORM\Index(columns: ['is_active'], name: 'idx_programmation_rule_slot_active')]
#[ORM\Index(columns: ['deleted_at'], name: 'idx_programmation_rule_slot_deleted')]
class ProgrammationRuleSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ProgrammationRule::class, inversedBy: 'slots')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ProgrammationRule $rule = null;

    /**
     * 1 = lundi, 7 = dimanche
     */
    #[ORM\Column(name: 'day_of_week')]
    private ?int $dayOfWeek = null;

    #[ORM\Column(type: 'time_immutable')]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column]
    private ?int $durationMinutes = null;

    /**
     * 1 = 1re diffusion
     * 2 = rediffusion 1
     * 3 = rediffusion 2
     * etc.
     */
    #[ORM\Column(name: 'broadcast_rank')]
    private int $broadcastRank = 1;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s %s',
            $this->getBroadcastLabel(),
            $this->getDayLabel(),
            $this->startTime?->format('H:i') ?? '--:--'
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRule(): ?ProgrammationRule
    {
        return $this->rule;
    }

    public function setRule(?ProgrammationRule $rule): static
    {
        $this->rule = $rule;
        $this->touch();

        return $this;
    }

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(?int $dayOfWeek): static
    {
        if ($dayOfWeek !== null && ($dayOfWeek < 1 || $dayOfWeek > 7)) {
            throw new \InvalidArgumentException('dayOfWeek doit être compris entre 1 (lundi) et 7 (dimanche).');
        }

        $this->dayOfWeek = $dayOfWeek;
        $this->touch();

        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;
        $this->touch();

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        if ($durationMinutes !== null && $durationMinutes <= 0) {
            throw new \InvalidArgumentException('durationMinutes doit être supérieur à 0.');
        }

        $this->durationMinutes = $durationMinutes;
        $this->touch();

        return $this;
    }

    public function getBroadcastRank(): int
    {
        return $this->broadcastRank;
    }

    public function setBroadcastRank(int $broadcastRank): static
    {
        if ($broadcastRank < 1) {
            throw new \InvalidArgumentException('broadcastRank doit être supérieur ou égal à 1.');
        }

        $this->broadcastRank = $broadcastRank;
        $this->touch();

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        $this->touch();

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        if ($deletedAt !== null) {
            $this->isActive = false;
        }

        $this->touch();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function softDelete(): static
    {
        $this->deletedAt = new \DateTimeImmutable();
        $this->isActive = false;
        $this->touch();

        return $this;
    }

    public function restore(): static
    {
        $this->deletedAt = null;
        $this->isActive = true;
        $this->touch();

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function getBroadcastLabel(): string
    {
        return match ($this->broadcastRank) {
            1 => '1re diffusion',
            2 => 'Rediffusion 1',
            3 => 'Rediffusion 2',
            default => 'Rediffusion ' . ($this->broadcastRank - 1),
        };
    }

    public function getDayLabel(): string
    {
        return match ($this->dayOfWeek) {
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
            default => 'Jour inconnu',
        };
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        if ($this->startTime === null || $this->durationMinutes === null) {
            return null;
        }

        return $this->startTime->modify(sprintf('+%d minutes', $this->durationMinutes));
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}