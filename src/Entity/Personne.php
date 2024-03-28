<?php

namespace App\Entity;

use App\Repository\PersonneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: PersonneRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Personne implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
   
    private ?string $prenom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datenaissance = null;

    #[ORM\Column(length: 100)]
    private ?string $lieunaissance = null;

    #[ORM\Column(length: 30)]
    private ?string $telephone = null;
    
    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'Habitant', targetEntity: Demandeinscription::class)]
    private Collection $HabitantDemandeinscription;

    #[ORM\OneToMany(mappedBy: 'Habitant', targetEntity: Demandecretificat::class)]
    private Collection $Habitantdemandecertificat;

    #[ORM\OneToMany(mappedBy: 'delegue', targetEntity: Demandeinscription::class)]
    private Collection $deleguedemandeinscription;

    #[ORM\OneToMany(mappedBy: 'delegue', targetEntity: Demandecretificat::class)]
    private Collection $deleguedemandecertificat;

    #[ORM\ManyToMany(targetEntity: Quartier::class, inversedBy: 'delegues')]
    private Collection $deleguequartier;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'mairenommes')]
    private ?self $deleguenommeur = null;

    #[ORM\OneToMany(mappedBy: 'deleguenommeur', targetEntity: self::class)]
    private Collection $mairenommes;

    #[ORM\ManyToMany(targetEntity: Commune::class, inversedBy: 'maires')]
    private Collection $maire;

    #[ORM\Column(length: 50)]
    private ?string $fonction = null;

   

    public function __construct()
    {
        $this->HabitantDemandeinscription = new ArrayCollection();
        $this->Habitantdemandecertificat = new ArrayCollection();
        $this->deleguedemandeinscription = new ArrayCollection();
        $this->deleguedemandecertificat = new ArrayCollection();
        $this->deleguequartier = new ArrayCollection();
        $this->mairenommes = new ArrayCollection();
        $this->maire = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDatenaissance(): ?\DateTimeInterface
    {
        return $this->datenaissance;
    }

    public function setDatenaissance(\DateTimeInterface $datenaissance): self
    {
        $this->datenaissance = $datenaissance;

        return $this;
    }

    public function getLieunaissance(): ?string
    {
        return $this->lieunaissance;
    }

    public function setLieunaissance(string $lieunaissance): self
    {
        $this->lieunaissance = $lieunaissance;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return Collection<int, Demandeinscription>
     */
    public function getHabitantDemandeinscription(): Collection
    {
        return $this->HabitantDemandeinscription;
    }

    public function addHabitantDemandeinscription(Demandeinscription $habitantDemandeinscription): self
    {
        if (!$this->HabitantDemandeinscription->contains($habitantDemandeinscription)) {
            $this->HabitantDemandeinscription->add($habitantDemandeinscription);
            $habitantDemandeinscription->setHabitant($this);
        }

        return $this;
    }

    public function removeHabitantDemandeinscription(Demandeinscription $habitantDemandeinscription): self
    {
        if ($this->HabitantDemandeinscription->removeElement($habitantDemandeinscription)) {
            // set the owning side to null (unless already changed)
            if ($habitantDemandeinscription->getHabitant() === $this) {
                $habitantDemandeinscription->setHabitant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Demandecretificat>
     */
    public function getHabitantdemandecertificat(): Collection
    {
        return $this->Habitantdemandecertificat;
    }

    public function addHabitantdemandecertificat(Demandecretificat $habitantdemandecertificat): self
    {
        if (!$this->Habitantdemandecertificat->contains($habitantdemandecertificat)) {
            $this->Habitantdemandecertificat->add($habitantdemandecertificat);
            $habitantdemandecertificat->setHabitant($this);
        }

        return $this;
    }

    public function removeHabitantdemandecertificat(Demandecretificat $habitantdemandecertificat): self
    {
        if ($this->Habitantdemandecertificat->removeElement($habitantdemandecertificat)) {
            // set the owning side to null (unless already changed)
            if ($habitantdemandecertificat->getHabitant() === $this) {
                $habitantdemandecertificat->setHabitant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Demandeinscription>
     */
    public function getDeleguedemandeinscription(): Collection
    {
        return $this->deleguedemandeinscription;
    }

    public function addDeleguedemandeinscription(Demandeinscription $deleguedemandeinscription): self
    {
        if (!$this->deleguedemandeinscription->contains($deleguedemandeinscription)) {
            $this->deleguedemandeinscription->add($deleguedemandeinscription);
            $deleguedemandeinscription->setDelegue($this);
        }

        return $this;
    }

    public function removeDeleguedemandeinscription(Demandeinscription $deleguedemandeinscription): self
    {
        if ($this->deleguedemandeinscription->removeElement($deleguedemandeinscription)) {
            // set the owning side to null (unless already changed)
            if ($deleguedemandeinscription->getDelegue() === $this) {
                $deleguedemandeinscription->setDelegue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Demandecretificat>
     */
    public function getDeleguedemandecertificat(): Collection
    {
        return $this->deleguedemandecertificat;
    }

    public function addDeleguedemandecertificat(Demandecretificat $deleguedemandecertificat): self
    {
        if (!$this->deleguedemandecertificat->contains($deleguedemandecertificat)) {
            $this->deleguedemandecertificat->add($deleguedemandecertificat);
            $deleguedemandecertificat->setDelegue($this);
        }

        return $this;
    }

    public function removeDeleguedemandecertificat(Demandecretificat $deleguedemandecertificat): self
    {
        if ($this->deleguedemandecertificat->removeElement($deleguedemandecertificat)) {
            // set the owning side to null (unless already changed)
            if ($deleguedemandecertificat->getDelegue() === $this) {
                $deleguedemandecertificat->setDelegue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Quartier>
     */
    public function getDeleguequartier(): Collection
    {
        return $this->deleguequartier;
    }

    public function addDeleguequartier(Quartier $deleguequartier): self
    {
        if (!$this->deleguequartier->contains($deleguequartier)) {
            $this->deleguequartier->add($deleguequartier);
        }

        return $this;
    }

    public function removeDeleguequartier(Quartier $deleguequartier): self
    {
        $this->deleguequartier->removeElement($deleguequartier);

        return $this;
    }

    public function getDeleguenommeur(): ?self
    {
        return $this->deleguenommeur;
    }

    public function setDeleguenommeur(?self $deleguenommeur): self
    {
        $this->deleguenommeur = $deleguenommeur;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getMairenommes(): Collection
    {
        return $this->mairenommes;
    }

    public function addMairenomme(self $mairenomme): self
    {
        if (!$this->mairenommes->contains($mairenomme)) {
            $this->mairenommes->add($mairenomme);
            $mairenomme->setDeleguenommeur($this);
        }

        return $this;
    }

    public function removeMairenomme(self $mairenomme): self
    {
        if ($this->mairenommes->removeElement($mairenomme)) {
            // set the owning side to null (unless already changed)
            if ($mairenomme->getDeleguenommeur() === $this) {
                $mairenomme->setDeleguenommeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commune>
     */
    public function getMaire(): Collection
    {
        return $this->maire;
    }

    public function addMaire(Commune $maire): self
    {
        if (!$this->maire->contains($maire)) {
            $this->maire->add($maire);
        }

        return $this;

    }

    public function removeMaire(Commune $maire): self
    {
        $this->maire->removeElement($maire);

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }
}
