<?php

namespace App\Entity;

use App\Repository\InviteOldAnimateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
    private ?string $firstName = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $mail = null;

    /**
     * @var Collection<int, Emission>
     */
    #[ORM\ManyToMany(targetEntity: Emission::class, mappedBy: 'InviteOldAnimateurs')]
    private Collection $emissions;

    #[ORM\Column()]
    private ?bool $ancienanimateur = null;

    public function __construct()
    {
        $this->emissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

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

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

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

    public function isAncienanimateur(): ?bool
    {
        return $this->ancienanimateur;
    }

    public function setAncienanimateur(bool $ancienanimateur): static
    {
        $this->ancienanimateur = $ancienanimateur;

        return $this;
    }
}
