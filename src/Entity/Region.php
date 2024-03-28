<?php

namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;


#[ORM\Entity(repositoryClass: RegionRepository::class)]
class Region
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    
    private ?int $id = null;
    


    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Departement::class)]
    private Collection $departements;
    
    public function __construct()
    {
        $this->departements = new ArrayCollection();
      //  $this->entityManager = $entityManager;
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
     * @return Collection<int, Departement>
     */
    public function getDepartements(): Collection
    {
        return $this->departements;
    }

    public function addDepartement(Departement $departement): self
    {
        if (!$this->departements->contains($departement)) {
            $this->departements->add($departement);
            $departement->setRegion($this);
        }

        return $this;
    }

    public function removeDepartement(Departement $departement): self
    {
        if ($this->departements->removeElement($departement)) {
            // set the owning side to null (unless already changed)
            if ($departement->getRegion() === $this) {
                $departement->setRegion(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->nom;
    }
    /**
     * @ Assert\CallBack
     */
     
     
    /*public function validateUniqueName($name, ExecutionContextInterface $context)
    {
        
        $entityManager = $this->getEntityManager();
    
        $existingCategory = $entityManager
            ->getRepository(Region::class)
            ->findOneBy(['nom' => $name]);
    
        if ($existingCategory && $existingCategory->getId() !== $this->getId()) {
            $context->buildViolation('This category name is already in use.')
                ->atPath('nom')
                ->addViolation();
        }
    }
    public function configureFields(string $pageName): iterable
{
    // ...

    yield TextField::new('name')
        ->setFormTypeOption('constraints', [
            new Assert\NotBlank(),
            new Assert\Length(['max' => 255]),
            new Assert\Callback([$this, 'validateUniqueName']),
        ]);

    // ...
}*/
   
    
}
