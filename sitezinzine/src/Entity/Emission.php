<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EmissionRepository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: EmissionRepository::class)]
#[Vich\Uploadable()]
class Emission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['emissions.index', 'emissions.lastemissions'])]
    private ?int $id = null;

    #[Groups(['emissions.index', 'emissions.create', 'emissions.lastemissions'])]
    #[ORM\Column(length: 250)]
    private string $titre = '';

    #[Groups(['emissions.index', 'emissions.create'])]
    #[ORM\Column(length: 250)]
    private ?string $keyword = null;

    #[Groups(['emissions.index', 'emissions.lastemissions'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datepub = null;

    #[Groups(['emissions.index', 'emissions.create'])]
    #[ORM\Column(length: 250)]
    private ?string $ref = null;

    #[ORM\Column]
    #[Positive()]
    #[Assert\NotBlank()]
    #[Assert\LessThan(value: 240)]
    #[Groups(['emissions.index', 'emissions.create', 'emissions.lastemissions'])]
    private ?int $duree = null;

    #[ORM\Column(length: 250)]
    #[Assert\Url(message: 'This value is not a valid URL')]
    #[Groups(['emissions.index', 'emissions.create', 'emissions.lastemissions'])]
    private string $url;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['emissions.index', 'emissions.create', 'emissions.lastemissions'])]
    private string $descriptif = '';

    #[ORM\ManyToOne(inversedBy: 'emissions', cascade: ['persist'])]
    #[Groups(['emissions.index', 'emissions.create'])]
    private ?Categories $categorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['emissions.index', 'emissions.create'])]
    private ?string $thumbnail = null;

    #[Vich\UploadableField(mapping: 'emissions', fileNameProperty: 'thumbnail')]
    #[Assert\Image()] //ajouter les contraintes d'image ici voir doc
    #[Groups(['emissions.index', 'emissions.create'])]
    private ?File $thumbnailFile = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['emissions.index'])]
    private ?\DateTimeInterface $updatedat = null;

    #[ORM\ManyToOne(inversedBy: 'emissions')]
    private ?Theme $theme = null;

    #[ORM\ManyToOne(inversedBy: 'emissions')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'emissions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Editeur $editeur = null;

    /**
     * @var Collection<int, Invite>
     */
    #[ORM\ManyToMany(targetEntity: Invite::class, inversedBy: 'emissions')]
    private Collection $invites;

    public function __construct()
    {
        $this->invites = new ArrayCollection();
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

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): static
    {
        $this->thumbnail = trim($thumbnail);

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
            $this->updatedat = new \DateTime();
        }

        return $this;
    }

    public function getUpdatedat(): ?\DateTimeInterface
    {
        return $this->updatedat;
    }

    public function setUpdatedat(?\DateTimeInterface $updatedat): static
    {
        $this->updatedat = $updatedat;

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEditeur(): ?Editeur
    {
        return $this->editeur;
    }

    public function setEditeur(?Editeur $editeur): static
    {
        $this->editeur = $editeur;

        return $this;
    }

    /**
     * @return Collection<int, Invite>
     */
    public function getInvites(): Collection
    {
        return $this->invites;
    }

    public function addInvite(Invite $invite): static
    {
        if (!$this->invites->contains($invite)) {
            $this->invites->add($invite);
        }

        return $this;
    }

    public function removeInvite(Invite $invite): static
    {
        $this->invites->removeElement($invite);

        return $this;
    }


}
