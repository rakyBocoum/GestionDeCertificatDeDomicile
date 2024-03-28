<?php

namespace App\Entity;

use App\Repository\DemandecretificatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandecretificatRepository::class)]
class Demandecretificat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $datedemande = null;

    #[ORM\Column]
    private ?int $montant = null;

    #[ORM\ManyToOne(inversedBy: 'Habitantdemandecertificat')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Personne $Habitant = null;

    #[ORM\ManyToOne(inversedBy: 'demandecertificat')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quartier $quartier = null;

    #[ORM\ManyToOne(inversedBy: 'deleguedemandecertificat')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Personne $delegue = null;

    #[ORM\Column(length: 20)]
    private ?string $etatdemande = null;

    #[ORM\Column(length: 255)]
    private ?string $idQrcode = null;

    #[ORM\ManyToOne(inversedBy: 'demandecretificats')]
    private ?Demandeinscription $demandeInscription = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): self
    {
        $this->montant = $montant;

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

    public function getEtatdemande(): ?string
    {
        return $this->etatdemande;
    }

    public function setEtatdemande(string $etatdemande): self
    {
        $this->etatdemande = $etatdemande;

        return $this;
    }

    public function getIdQrcode(): ?string
    {
        return $this->idQrcode;
    }

    public function setIdQrcode(string $idQrcode): self
    {
        $this->idQrcode = $idQrcode;

        return $this;
    }

    public function getDemandeInscription(): ?Demandeinscription
    {
        return $this->demandeInscription;
    }

    public function setDemandeInscription(?Demandeinscription $demandeInscription): self
    {
        $this->demandeInscription = $demandeInscription;

        return $this;
    }
}
