<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: CategoriesRepository::class)]
#[UniqueEntity('titre')]
#[Vich\Uploadable()]
class Categories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['categories.index'])]
    private ?int $id = null;

    #[ORM\Column(length: 35)]
    #[Groups(['categories.index', 'emissions.index'])]
    private string $titre = '';

    #[ORM\Column( nullable: true)]
    #[Groups(['categories.index'])]
    private ?int $oldid = null;

    #[ORM\Column]
    private ?int $editeur = null;

    #[ORM\Column]
    #[Groups(['categories.index'])]
    #[Assert\LessThan(value: 720)]
    private ?int $duree = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['categories.index'])]
    private ?string $descriptif = null;

    /**
     * @var Collection<int, Emission>
     */
    #[ORM\OneToMany(targetEntity: Emission::class, mappedBy: 'categorie')]
    private Collection $emissions;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['categories.index'])]
    private ?string $thumbnail = null;

    #[Vich\UploadableField(mapping: 'categories', fileNameProperty: 'thumbnail')]
    #[Assert\Image()] //ajouter les contraintes d'image ici voir doc
    #[Groups(['categories.index'])]
    private ?File $thumbnailFile = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['categories.index'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?bool $active = null;

    public function __construct()
    {
        $this->emissions = new ArrayCollection();
    }


    
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

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getThumbnailFile(): ?File
    {
        return $this->thumbnailFile;
    }

    public function setThumbnailFile(?File $thumbnailFile): static
    {
        $this->thumbnailFile = $thumbnailFile;

        if (null !== $thumbnailFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

}
