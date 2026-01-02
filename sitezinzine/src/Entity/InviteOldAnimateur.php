<?php

namespace App\Entity;

use App\Repository\InviteOldAnimateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InviteOldAnimateurRepository::class)]
class InviteOldAnimateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Le prénom est obligatoire.',
        normalizer: 'trim'
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mail = null;

    #[ORM\Column]
    private ?bool $ancienanimateur = null;

    /**
     * @var Collection<int, Emission>
     */
    #[ORM\ManyToMany(targetEntity: Emission::class, mappedBy: 'inviteOldAnimateurs')]
    private Collection $emissions;

    /**
     * @var Collection<int, Categories>
     */
    #[ORM\ManyToMany(targetEntity: Categories::class, mappedBy: 'inviteOldAnimateurs')]
    private Collection $categories;

    public function __construct()
    {
        $this->emissions = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    /**
     * Utilisé par Symfony / Twig / ChoiceType
     */
    public function __toString(): string
    {
        $fullName = trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
        return $fullName !== '' ? $fullName : 'Invité #' . ($this->id ?? '—');
    }

    /* =========================
     * Getters / Setters simples
     * ========================= */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;
        return $this;
    }

    public function isAncienanimateur(): ?bool
    {
        return $this->ancienanimateur;
    }

    public function setAncienanimateur(?bool $ancienanimateur): static
    {
        $this->ancienanimateur = $ancienanimateur;
        return $this;
    }

    /* =========================
     * Relations : Emissions
     * ========================= */

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
            $emission->addInviteOldAnimateur($this);
        }

        return $this;
    }

    public function removeEmission(Emission $emission): static
    {
        if ($this->emissions->removeElement($emission)) {
            $emission->removeInviteOldAnimateur($this);
        }

        return $this;
    }

    /* =========================
     * Relations : Categories
     * ========================= */

    /**
     * @return Collection<int, Categories>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Categories $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addInviteOldAnimateur($this);
        }

        return $this;
    }

    public function removeCategory(Categories $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeInviteOldAnimateur($this);
        }

        return $this;
    }
}
