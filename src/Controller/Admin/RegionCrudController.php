<?php

namespace App\Controller\Admin;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Region;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;


class RegionCrudController extends AbstractCrudController
{
  
    public static function getEntityFqcn(): string
    {
        return Region::class;
    }

   
   

    public function configureFields(string $pageName): iterable

    {
        return 
        [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom')->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255]),
                //new Assert\Callback([$this, 'validateUniqueName']),
            ]),
            
        ];
    }
    public function deleteEntity(EntityManagerInterface $em, $entity): void
    {
        if(!$entity instanceof Region)
        return;
        foreach($entity->getDepartements() as $departement)
        {
            $em ->remove($departement);
        }
        parent::deleteEntity($em,$entity);
    }

   /* public function configureCrud(Crud $crud): Crud
    {
        // ...

        $crud->setFormOptions([
            'constraints' => [
                new UniqueEntity(['fields' => 'name'])
            ]
        ]);

        return $crud;
    }
    */

}
