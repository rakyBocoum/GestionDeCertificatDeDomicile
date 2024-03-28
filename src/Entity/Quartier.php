<?php

namespace App\Entity;

use App\Repository\QuartierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuartierRepository::class)]
class Quartier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column] 
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'quartier', targetEntity: Demandeinscription::class)]
    private Collection $demandeinscription;

    #[ORM\OneToMany(mappedBy: 'quartier', targetEntity: Demandecretificat::class)]
    private Collection $demandecertificat;

    #[ORM\ManyToMany(targetEntity: Personne::class, mappedBy: 'deleguequartier')]
    private Collection $delegues;

    #[ORM\ManyToOne(inversedBy: 'quartiers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commune $commune = null;

    public function __construct()
    {
        $this->demandeinscription = new ArrayCollection();
        $this->demandecertificat = new ArrayCollection();
        $this->delegues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Demandeinscription>
     */
    public function getDemandeinscription(): Collection
    {
        return $this->demandeinscription;
    }

    public function addDemandeinscription(Demandeinscription $demandeinscription): self
    {
        if (!$this->demandeinscription->contains($demandeinscription)) {
            $this->demandeinscription->add($demandeinscription);
            $demandeinscription->setQuartier($this);
        }

        return $this;
    }

    public function removeDemandeinscription(Demandeinscription $demandeinscription): self
    {
        if ($this->demandeinscription->removeElement($demandeinscription)) {
            // set the owning side to null (unless already changed)
            if ($demandeinscription->getQuartier() === $this) {
                $demandeinscription->setQuartier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Demandecretificat>
     */
    public function getDemandecertificat(): Collection
    {
        return $this->demandecertificat;
    }

    public function addDemandecertificat(Demandecretificat $demandecertificat): self
    {
        if (!$this->demandecertificat->contains($demandecertificat)) {
            $this->demandecertificat->add($demandecertificat);
            $demandecertificat->setQuartier($this);
        }

        return $this;
    }

    public function removeDemandecertificat(Demandecretificat $demandecertificat): self
    {
        if ($this->demandecertificat->removeElement($demandecertificat)) {
            // set the owning side to null (unless already changed)
            if ($demandecertificat->getQuartier() === $this) {
                $demandecertificat->setQuartier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getDelegues(): Collection
    {
        return $this->delegues;
    }

    public function addDelegue(Personne $delegue): self
    {
        if (!$this->delegues->contains($delegue)) {
            $this->delegues->add($delegue);
            $delegue->addDeleguequartier($this);
        }

        return $this;
    }

    public function removeDelegue(Personne $delegue): self
    {
        if ($this->delegues->removeElement($delegue)) {
            $delegue->removeDeleguequartier($this);
        }

        return $this;
    }

    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune(?Commune $commune): self
    {
        $this->commune = $commune;

        return $this;
    }
    public function __toString()
    {
        return $this->nom;
    }
}
