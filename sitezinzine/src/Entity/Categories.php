<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategoriesRepository::class)]
#[UniqueEntity('titre')]
class Categories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 250)]
    private ?string $titre = null;

    #[ORM\Column]
    private ?int $oldid = null;

    #[ORM\Column]
    private ?int $editeur = null;

    #[ORM\Column]
    #[Assert\LessThan(value: 720)]
    private ?int $duree = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descriptif = null;

    /**
     * @var Collection<int, Emission>
     */
    #[ORM\OneToMany(targetEntity: Emission::class, mappedBy: 'categorie')]
    private Collection $emissions;

    public function __construct()
    {
        $this->emissions = new ArrayCollection();
    }


    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getOldid(): ?int
    {
        return $this->oldid;
    }

    public function setOldid(int $oldid): static
    {
        $this->oldid = $oldid;

        return $this;
    }

    public function getEditeur(): ?int
    {
        return $this->editeur;
    }

    public function setEditeur(int $editeur): static
    {
        $this->editeur = $editeur;

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

    public function getDescriptif(): ?string
    {
        return $this->descriptif;
    }

    public function setDescriptif(string $descriptif): static
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    /**
     * @return Collection<int, Emission>
     */
    public function getEmissions(): Collection
    {
        return $this->emissions;
    }

    public function addEmission(Emission $emission): static
    {
        if (!$this->emissions->contains($emission)) {
            $this->emissions->add($emission);
            $emission->setCategorie($this);
        }

        return $this;
    }

    public function removeEmission(Emission $emission): static
    {
        if ($this->emissions->removeElement($emission)) {
            // set the owning side to null (unless already changed)
            if ($emission->getCategorie() === $this) {
                $emission->setCategorie(null);
            }
        }

        return $this;
    }

}
