<?php

namespace App\Entity;

use App\Repository\GridSlotArbitrationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GridSlotArbitrationRepository::class)]
#[ORM\Table(name: 'grid_slot_arbitration')]
#[ORM\UniqueConstraint(
    name: 'uniq_grid_slot_arbitration_slot_start',
    columns: ['slot_id', 'starts_at']
)]
#[ORM\Index(name: 'idx_grid_slot_arbitration_status', columns: ['status'])]
#[ORM\Index(name: 'idx_grid_slot_arbitration_starts_at', columns: ['starts_at'])]
#[ORM\Index(name: 'idx_grid_slot_arbitration_needs_reschedule', columns: ['needs_reschedule'])]
#[ORM\Index(name: 'idx_grid_slot_arbitration_conflict_type', columns: ['conflict_type'])]
class GridSlotArbitration
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CANCELLED = 'cancelled';

    public const CONFLICT_TYPE_RULE_OVERLAP = 'rule_overlap';
    public const CONFLICT_TYPE_SPECIAL_EVENT_OVERRIDE = 'special_event_override';
    public const CONFLICT_TYPE_MANUAL_OVERRIDE = 'manual_override';

    public const RESOLUTION_ACTION_KEEP = 'keep';
    public const RESOLUTION_ACTION_REPLACE = 'replace';
    public const RESOLUTION_ACTION_CANCEL = 'cancel';
    public const RESOLUTION_ACTION_RESCHEDULE = 'reschedule';

    public const RESCHEDULE_STATUS_PENDING = 'pending';
    public const RESCHEDULE_STATUS_DONE = 'done';
    public const RESCHEDULE_STATUS_ABANDONED = 'abandoned';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ProgrammationRuleSlot::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ProgrammationRuleSlot $slot = null;

    #[ORM\Column(name: 'starts_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column(name: 'ends_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(length: 40)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(name: 'conflict_type', length: 50)]
    private string $conflictType = self::CONFLICT_TYPE_RULE_OVERLAP;

    #[ORM\Column(name: 'resolution_action', length: 40, nullable: true)]
    private ?string $resolutionAction = null;

    #[ORM\Column(name: 'needs_reschedule')]
    private bool $needsReschedule = false;

    #[ORM\Column(name: 'reschedule_status', length: 20, nullable: true)]
    private ?string $rescheduleStatus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    /**
     * Référence vers le créneau d’arbitrage qui a “pris la place”
     * de celui-ci, si on veut garder une trace de remplacement.
     */
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'replaced_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?self $replacedBy = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlot(): ?ProgrammationRuleSlot
    {
        return $this->slot;
    }

    public function setSlot(?ProgrammationRuleSlot $slot): static
    {
        $this->slot = $slot;
        $this->touch();

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;
        $this->touch();

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeImmutable $endsAt): static
    {
        if ($this->startsAt !== null && $endsAt <= $this->startsAt) {
            throw new \InvalidArgumentException('endsAt doit être strictement supérieur à startsAt.');
        }

        $this->endsAt = $endsAt;
        $this->touch();

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $allowed = [
            self::STATUS_PENDING,
            self::STATUS_RESOLVED,
            self::STATUS_CANCELLED,
        ];

        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('status invalide.');
        }

        $this->status = $status;
        $this->touch();

        return $this;
    }

    public function getConflictType(): string
    {
        return $this->conflictType;
    }

    public function setConflictType(string $conflictType): static
    {
        $allowed = [
            self::CONFLICT_TYPE_RULE_OVERLAP,
            self::CONFLICT_TYPE_SPECIAL_EVENT_OVERRIDE,
            self::CONFLICT_TYPE_MANUAL_OVERRIDE,
        ];

        if (!in_array($conflictType, $allowed, true)) {
            throw new \InvalidArgumentException('conflictType invalide.');
        }

        $this->conflictType = $conflictType;
        $this->touch();

        return $this;
    }

    public function getResolutionAction(): ?string
    {
        return $this->resolutionAction;
    }

    public function setResolutionAction(?string $resolutionAction): static
    {
        $allowed = [
            null,
            self::RESOLUTION_ACTION_KEEP,
            self::RESOLUTION_ACTION_REPLACE,
            self::RESOLUTION_ACTION_CANCEL,
            self::RESOLUTION_ACTION_RESCHEDULE,
        ];

        if (!in_array($resolutionAction, $allowed, true)) {
            throw new \InvalidArgumentException('resolutionAction invalide.');
        }

        $this->resolutionAction = $resolutionAction;
        $this->touch();

        return $this;
    }

    public function needsReschedule(): bool
    {
        return $this->needsReschedule;
    }

    public function getNeedsReschedule(): bool
    {
        return $this->needsReschedule;
    }

    public function setNeedsReschedule(bool $needsReschedule): static
    {
        $this->needsReschedule = $needsReschedule;

        if ($needsReschedule === false) {
            $this->rescheduleStatus = null;
        } elseif ($this->rescheduleStatus === null) {
            $this->rescheduleStatus = self::RESCHEDULE_STATUS_PENDING;
        }

        $this->touch();

        return $this;
    }

    public function getRescheduleStatus(): ?string
    {
        return $this->rescheduleStatus;
    }

    public function setRescheduleStatus(?string $rescheduleStatus): static
    {
        $allowed = [
            null,
            self::RESCHEDULE_STATUS_PENDING,
            self::RESCHEDULE_STATUS_DONE,
            self::RESCHEDULE_STATUS_ABANDONED,
        ];

        if (!in_array($rescheduleStatus, $allowed, true)) {
            throw new \InvalidArgumentException('rescheduleStatus invalide.');
        }

        $this->rescheduleStatus = $rescheduleStatus;
        $this->touch();

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;
        $this->touch();

        return $this;
    }

    public function getReplacedBy(): ?self
    {
        return $this->replacedBy;
    }

    public function setReplacedBy(?self $replacedBy): static
    {
        $this->replacedBy = $replacedBy;
        $this->touch();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function markResolved(
        string $resolutionAction,
        bool $needsReschedule = false,
        ?string $note = null
    ): static {
        $this->setStatus(self::STATUS_RESOLVED);
        $this->setResolutionAction($resolutionAction);
        $this->setNeedsReschedule($needsReschedule);
        $this->setNote($note);

        return $this;
    }

    public function cancel(?string $note = null): static
    {
        $this->setStatus(self::STATUS_CANCELLED);
        $this->setNote($note);

        return $this;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}