<?php

namespace App\Controller\Admin;

use App\Entity\Commune;
use App\Entity\Departement;
use App\Entity\Personne;
use Doctrine\ORM\Mapping\Builder\AssociationBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
//use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\Filter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;


class CommuneCrudController extends AbstractCrudController
{
    public const ACTION_NOMMER = 'nommerMaire';
    public static function getEntityFqcn(): string
    {
        return Commune::class;

    }
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('departement'),
            IdField::new('id')->hideOnForm(),
            TextField::new('nom'),
           
        ];
    
    }
    public function configureActions(Actions $actions): Actions
    {
        $actions0 = parent::configureActions($actions);
        $actions1 = parent::configureActions($actions);

           /* $actions0 =Action::new('ParDepartement')
            ->linkToCrudAction('index')
            ->setCssClass('btn btn-primary')
            ->setIcon('fa fa-tags')
            ->setLabel('Par Departement');*/
        
        $actions1=Action::new('Nommer un maire')
            ->linkToCrudAction('index')
            ->setCssClass('btn btn-primary')
            ->setIcon('fa fa-tags')
            ->setLabel('Nommer un maire');

    return $actions
            //->add(Crud::PAGE_INDEX, $actions0)
            ->add(Crud::PAGE_INDEX, $actions1);
    }
   
    public function findDepartementByRegion($departement)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $communeRepository = $entityManager->getRepository(Commune::class);

        $commune = $communeRepository->findBy(['departement' => $departement]);

        return $departement;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('departement'));
        
       
    }
    
   
    public function liste (AdminContext $context): Response
    {
        // retrieve the selected category from the filter
        $filters = $context->getFiltersState()->getFilters();
        $selectedDepartementId = $filters['departement']->getValue();

        // retrieve the products based on the selected category
        $entityManager = $this->getDoctrine()->getManager();
        $communeRepository = $entityManager->getRepository(Commune::class);
        $commune = $communeRepository->findBy(['departement' => $selectedDepartementId]);

        $context->setEntityPropertyValue('commune', $commune);

        return parent::index($context);
    }


   


    
    
   /* #[Route("/admin/commune/{id}", name:"admin_commune_show")]
    public function show($id): Response
    {
        return new Response('Commune id: '.$id);
    }*/

    

    public function nommerMaire():Response
    {
     die('bbbb');
    }
}
