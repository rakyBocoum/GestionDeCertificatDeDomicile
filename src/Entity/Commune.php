<?php

namespace App\Entity;

use App\Repository\CommuneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommuneRepository::class)]
class Commune
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

   

    #[ORM\ManyToMany(targetEntity: Personne::class, mappedBy: 'maire')]
    private Collection $maires;

    #[ORM\OneToMany(mappedBy: 'commune', targetEntity: Quartier::class)]
    private Collection $quartiers;

    #[ORM\ManyToOne(inversedBy: 'communes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Departement $departement = null;

    public function __construct()
    {
       
        $this->maires = new ArrayCollection();
        $this->quartiers = new ArrayCollection();
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
     * @return Collection<int, Personne>
     */
    public function getMaires(): Collection
    {
        return $this->maires;
    }

    public function addMaire(Personne $maire): self
    {
        if (!$this->maires->contains($maire)) {
            $this->maires->add($maire);
            $maire->addMaire($this);
        }

        return $this;
    }

    public function removeMaire(Personne $maire): self
    {
        if ($this->maires->removeElement($maire)) {
            $maire->removeMaire($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Quartier>
     */
    public function getQuartiers(): Collection
    {
        return $this->quartiers;
    }

    public function addQuartier(Quartier $quartier): self
    {
        if (!$this->quartiers->contains($quartier)) {
            $this->quartiers->add($quartier);
            $quartier->setCommune($this);
        }

        return $this;
    }

    public function removeQuartier(Quartier $quartier): self
    {
        if ($this->quartiers->removeElement($quartier)) {
            // set the owning side to null (unless already changed)
            if ($quartier->getCommune() === $this) {
                $quartier->setCommune(null);
            }
        }

        return $this;
    }

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): self
    {
        $this->departement = $departement;

        return $this;
    }
    public function __toString()
    {
        return $this->nom;
    }
}
