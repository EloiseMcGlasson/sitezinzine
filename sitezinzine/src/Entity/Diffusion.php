<?php

namespace App\Entity;

use App\Repository\DiffusionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiffusionRepository::class)]
class Diffusion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'nombreDiffusion')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emission $emission = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $horaireDiffusion = null;

    #[ORM\Column]
    private ?int $nombreDiffusion = null;

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

        return $this;
    }

    public function getHoraireDiffusion(): ?\DateTimeInterface
    {
        return $this->horaireDiffusion;
    }

    public function setHoraireDiffusion(\DateTimeInterface $horaireDiffusion): static
    {
        $this->horaireDiffusion = $horaireDiffusion;

        return $this;
    }

    public function getNombreDiffusion(): ?int
    {
        return $this->nombreDiffusion;
    }

    public function setNombreDiffusion(int $nombreDiffusion): static
    {
        $this->nombreDiffusion = $nombreDiffusion;

        return $this;
    }
}
