<?php

namespace App\Controller\Admin;

use App\Entity\Departement;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Region;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Builder\AssociationBuilder;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Controller\Admin\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\Filter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

class DepartementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Departement::class;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('region'),
            IdField::new('id')->hideOnform(),
            TextField::new('nom'),
            
           // TextEditorField::new('description'),
        ];
    }


   /* public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        return $actions->add(Crud::PAGE_INDEX, Action::new('ParRegion')
            ->linkToCrudAction('index')
            ->setCssClass('btn btn-primary')
            ->setIcon('fa fa-tags')
            ->setLabel('Par Region')
          
        );
    }*/


    public function findDepartementByRegion($region)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $departementRepository = $entityManager->getRepository(Departement::class);

        $departement = $departementRepository->findBy(['Region' => $region]);

        return $departement;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('region'));
        
       
    }
   
    public function liste (AdminContext $context): Response
    {
        // retrieve the selected category from the filter
        $filters = $context->getFiltersState()->getFilters();
        $selectedRegionId = $filters['region']->getValue();

        // retrieve the products based on the selected category
        $entityManager = $this->getDoctrine()->getManager();
        $departementRepository = $entityManager->getRepository(Produit::class);
        $departement = $departementRepository->findBy(['region' => $selectedRegionId]);

        $context->setEntityPropertyValue('departements', $departement);

        return parent::index($context);
    }
    public function deleteEntity(EntityManagerInterface $em, $entity): void
    {
        if(!$entity instanceof Departement)
        return;
        foreach($entity->getCommunes() as $departement)
        {
            $em ->remove($departement);
        }
        parent::deleteEntity($em,$entity);
    }



   
   /* public function lister  (EntityManagerInterface $entityManager, Request $request): Response
    {
        $region = $request->query->get('region');

        if ($region) {
            $departement = $this->findDepartementByRegion($region);
        } else {
            $departement = $this->getDoctrine()->getRepository(Departement::class)->findAll();
        }

        return $this->render('admin/departement/index.html.twig', [
            'departement' => $departement,
        ]);
    }*/
    
    
}
