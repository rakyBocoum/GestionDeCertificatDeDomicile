<?php

namespace App\Entity;

use App\Repository\DemandeinscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeinscriptionRepository::class)]
class Demandeinscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $etatdemande = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $datedemande = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $datetraitement = null;

    #[ORM\ManyToOne(inversedBy: 'HabitantDemandeinscription')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Personne $Habitant = null;

    #[ORM\ManyToOne(inversedBy: 'demandeinscription')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quartier $quartier = null;
    private ?Commune $commune = null;

    #[ORM\ManyToOne(inversedBy: 'deleguedemandeinscription')]
    private ?Personne $delegue = null;

    #[ORM\OneToMany(mappedBy: 'demandeInscription', targetEntity: Demandecretificat::class)]
    private Collection $demandecretificats;

    public function __construct()
    {
        $this->demandecretificats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtatdemande(): ?string
    {
        return $this->etatdemande;
    }

    public function setEtatdemande(string $etatdemande): self
    {
        $this->etatdemande = $etatdemande;

        return $this;
    }

    public function getDatedemande(): ?\DateTimeImmutable
    {
        return $this->datedemande;
    }

    public function setDatedemande(\DateTimeImmutable $datedemande): self
    {
        $this->datedemande = $datedemande;

        return $this;
    }

    public function getDatetraitement(): ?\DateTimeImmutable
    {
        return $this->datetraitement;
    }

    public function setDatetraitement(?\DateTimeImmutable $datetraitement): self
    {
        $this->datetraitement = $datetraitement;

        return $this;
    }

    public function getHabitant(): ?Personne
    {
        return $this->Habitant;
    }

    public function setHabitant(?Personne $Habitant): self
    {
        $this->Habitant = $Habitant;

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

    public function getQuartier(): ?Quartier
    {
        return $this->quartier;
    }

    public function setQuartier(?Quartier $quartier): self
    {
        $this->quartier = $quartier;

        return $this;
    }
    

    public function getDelegue(): ?Personne
    {
        return $this->delegue;
    }

    public function setDelegue(?Personne $delegue): self
    {
        $this->delegue = $delegue;

        return $this;
    }

    /**
     * @return Collection<int, Demandecretificat>
     */
    public function getDemandecretificats(): Collection
    {
        return $this->demandecretificats;
    }

    public function addDemandecretificat(Demandecretificat $demandecretificat): self
    {
        if (!$this->demandecretificats->contains($demandecretificat)) {
            $this->demandecretificats->add($demandecretificat);
            $demandecretificat->setDemandeInscription($this);
        }

        return $this;
    }

    public function removeDemandecretificat(Demandecretificat $demandecretificat): self
    {
        if ($this->demandecretificats->removeElement($demandecretificat)) {
            // set the owning side to null (unless already changed)
            if ($demandecretificat->getDemandeInscription() === $this) {
                $demandecretificat->setDemandeInscription(null);
            }
        }

        return $this;
    }
}
