<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EmissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Positive;

#[ORM\Entity(repositoryClass: EmissionRepository::class)]
class Emission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 250)]
    private string $titre = '';

    #[ORM\Column(length: 250)]
    private ?string $keyword = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datepub = null;

    #[ORM\Column(length: 250)]
    private ?string $ref = null;

    #[ORM\Column]
    #[Positive()]
    #[Assert\NotBlank()]
    #[Assert\LessThan(value: 720)]
    private ?int $duree = null;

    #[ORM\Column(length: 250)]
    #[Assert\Url(message: 'This value is not a valid URL')]
    private ?string $url = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $descriptif = '';

    #[ORM\ManyToOne(inversedBy: 'emissions')]
    private ?Categories $categorie = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(string $keyword): static
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function getDatepub(): ?\DateTimeInterface
    {
        return $this->datepub;
    }

    public function setDatepub(\DateTimeInterface $datepub): static
    {
        $this->datepub = $datepub;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): static
    {
        $this->ref = $ref;

        return $this;
    }


    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getDescriptif(): string
    {
        return $this->descriptif;
    }

    public function setDescriptif(string $descriptif): static
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    public function getCategorie(): ?Categories
    {
        return $this->categorie;
    }

    public function setCategorie(?Categories $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }


}
