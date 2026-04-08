<?php

namespace App\Entity;

use App\Repository\DiffusionDraftRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiffusionDraftRepository::class)]
#[ORM\Table(name: 'diffusion_draft')]
#[ORM\UniqueConstraint(name: 'uniq_diffusion_draft_slot_horaire', columns: ['slot_id', 'horaire_diffusion'])]
#[ORM\Index(name: 'idx_diffusion_draft_horaire', columns: ['horaire_diffusion'])]
#[ORM\Index(name: 'idx_diffusion_draft_emission', columns: ['emission_id'])]
#[ORM\Index(name: 'idx_diffusion_draft_slot', columns: ['slot_id'])]
class DiffusionDraft
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Emission::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Emission $emission = null;

    #[ORM\ManyToOne(targetEntity: ProgrammationRuleSlot::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ProgrammationRuleSlot $slot = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $horaireDiffusion = null;

    /**
     * 1 = première diffusion
     * 2 = rediffusion 1
     * 3 = rediffusion 2
     * etc.
     */
    #[ORM\Column]
    private ?int $nombreDiffusion = null;

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

    public function getEmission(): ?Emission
    {
        return $this->emission;
    }

    public function setEmission(?Emission $emission): static
    {
        $this->emission = $emission;
        $this->touch();

        return $this;
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

    public function getHoraireDiffusion(): ?\DateTimeImmutable
    {
        return $this->horaireDiffusion;
    }

    public function setHoraireDiffusion(\DateTimeImmutable $horaireDiffusion): static
    {
        $this->horaireDiffusion = $horaireDiffusion;
        $this->touch();

        return $this;
    }

    public function getNombreDiffusion(): ?int
    {
        return $this->nombreDiffusion;
    }

    public function setNombreDiffusion(int $nombreDiffusion): static
    {
        if ($nombreDiffusion < 1) {
            throw new \InvalidArgumentException('nombreDiffusion doit être supérieur ou égal à 1.');
        }

        $this->nombreDiffusion = $nombreDiffusion;
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

    public function getRule(): ?ProgrammationRule
    {
        return $this->slot?->getRule();
    }

    public function getBroadcastRank(): ?int
    {
        return $this->nombreDiffusion;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}