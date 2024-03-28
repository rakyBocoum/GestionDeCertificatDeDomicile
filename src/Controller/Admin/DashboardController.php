<?php

namespace App\Controller\Admin;

use App\Entity\Commune;
use App\Entity\Departement;
use App\Entity\Personne;
use App\Entity\Demandeinscription;
use App\Entity\Region;
use App\Form\AjouterDelegueType;
use DateTimeImmutable;
use Symfony\Component\Mailer\MailerInterface;
use App\Repository\DemandeinscriptionRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\PersonneRepository;
use App\Repository\CommuneRepository;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class DashboardController extends AbstractDashboardController
{
    private $communeRepository;
    private $departementRepository;
    public function __construct(private AdminUrlGenerator $adminUrlGenerator, CommuneRepository $communeRepository, DepartementRepository $departementRepository )
    {
        $this->communeRepository = $communeRepository;
        $this->departementRepository = $departementRepository;
    }
    #[Route('/admin', name: 'admin')]
    public function index(): Response       
    {
        $url = $this->adminUrlGenerator
            ->setController(RegionCrudController::class)
            ->generateUrl();
            $route = 'admin_nommer_maire';

   
  
        //return parent::index();
        //return $this->redirect($url);
        return $this->redirect($url);
       
    }



    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Sama-Domicile');
    }

    
      #[Route("/admin/commune/lister", name:"app_admin_lister_commune")]
     
    public function CommuneList(PaginatorInterface $paginator,Request $request): Response
    {
        $commune = $this->communeRepository->findAll();
        $pagination = $paginator->paginate(
            $commune, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        $pagination->setPageRange(4);

        return $this->render('admin/listerCommune.html.twig', [
            'Commune' => $commune,['pagination' => $pagination]
        ]);
    }


    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        //Pour les regions
        yield MenuItem::section('Region');
        yield MenuItem::subMenu('Actions','fas fa-bars')->setSubItems([
               MenuItem::linkToCrud('Creer une region','fas fa-plus',Region::class)->setAction(Crud::PAGE_NEW),
               MenuItem::linkToCrud('Visualier les regions','fas fa-eye',Region::class)
        ]);
        //Pour les departements
        yield MenuItem::section('Departement');
        yield MenuItem::subMenu('Actions','fas fa-bars')->setSubItems([
               MenuItem::linkToCrud('Creer un departement','fas fa-plus',Departement::class)->setAction(Crud::PAGE_NEW),
               
               MenuItem::linkToCrud('Visualier les departements','fas fa-eye',Departement::class)
        ]);

        //Pour les communes
        yield MenuItem::section('Commune');
        yield MenuItem::subMenu('Actions','fas fa-bars')->setSubItems([
              MenuItem::linkToCrud('Creer une commune','fas fa-plus',Commune::class)->setAction(Crud::PAGE_NEW),
              MenuItem::linkToCrud('Visualiser les communes','fas fa-eye',Commune::class),
             MenuItem::linkToRoute('Nommer les maires ou Remplacer','fas fa-eye','app_admin_lister_commune'),
               //MenuItem::linkToCrud('Choisir un maire','fas fa-eye',CommuneCrudController::ACTION_NOMMER),
        ]);

       
        
    }
   /* #[Route("/admin/commune/{id}", name:"admin_commune_show")]
    public function show($id): Response
    {
        dd($id);
        //return new Response('Commune id: '.$id);
    }*/

  


        #[Route('/admin/ajouterunMaire/{id}', name: 'app_admin_ajouterMaire')]
        public function maireAjouterDelegue(Commune $id, CommuneRepository $communeRepository, DepartementRepository $departementRepository, PersonneRepository $personneRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, DemandeinscriptionRepository $demandeinscriptionRepository, MailerInterface $mailer)
        {
           
           
            //verifions si la commune est bien un quartier de la commune pour le maire
            $veirifier = $communeRepository->findOneBy(['id' => $id->getId()]);
        
            //verifions si la commune a un maire encours
            if (count($communeRepository->uneCommuneSansMaire($veirifier->getId())) != 0) {
                //donc ce quartier a deja un delgue impossible d'ajouter une autre
                $this->addFlash('info', ' Attention !cette commune a déjà un maire dont son mandat est en cours. Donc impossible 
                    d\'ajouter un autre néanmoins vous avez la possibilité de le remplacer . ');
                return $this->redirectToRoute('app_admin_lister_commune');
            }
            $form = $this->createForm(AjouterDelegueType::class);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $message = "";
                //cherchons si l'adresse mail de ce delegue est deja existante dans une autre quartiers
                //c a d si cette personne est deja delegue dans un quartier
                $email = $form->get('email')->getData();
                $maire = $personneRepository->findOneBy(['email' => $email]);
    
                //donc la personne exite dans la base de donnee
                if ($maire != null) {
                    if (count($personneRepository->jointuredelguequartier($maire->getId())) != 0) {
                        //donc la personne est actuellemnt maire dans une commune
                        $this->addFlash('info', ' Attention !la personne ayant comme adresse mail ' . $email .
                            ' est actuellement maire dans une commune. vous pouvez l\'ajouter comme maire avec une autre adresse email ');
                        return $this->render('admin/ajouterMaire.html.twig', [
                            'registrationForm' => $form->createView(),
                        ]);
                    }
                    if (count($personneRepository->jointuremairecommune($maire->getId())) != 0) {
                        //donc la personne est actuellemnt maire
                        $roles = $maire->getRoles();
    
                        $this->addFlash('info', ' Attention !la personne ayant comme adresse mail ' . $email .
                            ' est actuellement maire dans une commune .vous pouvez l\'ajouter comme délégué avec une autre adresse email ');
                        return $this->render('admin/ajouterMaire.html.twig', [
                            'registrationForm' => $form->createView(),
                        ]);
                    }
                    $maire->setNom($form->get('nom')->getData());
                    $maire->setPreNom($form->get('prenom')->getData());
                    $maire->setDatenaissance($form->get('datenaissance')->getData());
                    $maire->setLieunaissance($form->get('lieunaissance')->getData());
                    $maire->setTelephone($form->get('telephone')->getData());
                    $maire->setFonction($form->get('fonction')->getData());
                    $roles = ['ROLE_HABITANT', 'ROLE_DELEGUE'];
                    $maire->setRoles($roles);
    
                    //le maire doit etre donc un habitant de cette commune
                    $mairedemandenontraiter = $demandeinscriptionRepository->findOneBy(['Habitant' => $maire, 'etatdemande' => 'nonTraiter', 'quartier' => $veirifier]);
                    $mairedemandeaccepter = $demandeinscriptionRepository->findOneBy(['Habitant' => $maire, 'etatdemande' => 'accepter', 'quartier' => $veirifier]);
                    //donc le personne n'est pas un habitant de ce quartier
                   
    
                    $entityManager->persist($maire);
                    $communeRepository->ajouterUnMaire($veirifier->getId(), $maire->getId());
                    //envoi du message
                    $url = $this->generateUrl('app_login', [], urlGeneratorInterface::ABSOLUTE_URL);
                    $entityManager->flush();
                    
                    $message =
                        '<p>' 
                        
                        .' vous etes maire de la commune de ' . $id->getNom() . '</p>' .
                        '<p>Le compte pour gérer ce quartier est le même que vous avez créer avec cette adresse mail ' . $maire->getEmail() .
                        '<p>accéder à votre compte en cliquant sur ce lien <a href="' . $url . '">cliquer moi</a></p> ';
                    // dd($delegue);
                } else {
                    //on doit lui creer un compte et tout le reste
                    $user = new Personne();
                    $user->setNom($form->get('nom')->getData());
                    $user->setPreNom($form->get('prenom')->getData());
                    $user->setDatenaissance($form->get('datenaissance')->getData());
                    $user->setLieunaissance($form->get('lieunaissance')->getData());
                    $user->setTelephone($form->get('telephone')->getData());
                    $user->setFonction($form->get('fonction')->getData());
                    $user->setEmail($form->get('email')->getData());
                    $user->setIsVerified(1);
                    $roles = ['ROLE_HABITANT', 'ROLE_MAIRE'];
                    $user->setRoles($roles);
                    // encode the plain password
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('plainPassword')->getData()
                        )
                    );
    
    
                    // $user->setRoles($g);
                    //    $user->setDatenaissance(new Date());
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $demandeinscription = new Demandeinscription();
                    $demandeinscription->setDatedemande(new DateTimeImmutable());
                    $demandeinscription->setDatetraitement(new DateTimeImmutable());
                    $demandeinscription->setHabitant($user);
                    $demandeinscription->setDelegue($user);
                    $demandeinscription->setQuartier($veirifier);
                    $demandeinscription->setEtatdemande('accepter');
                    $entityManager->persist($demandeinscription);
                    $communeRepository->ajouterUnMaire($veirifier->getId(), $user->getId());
                    $entityManager->flush();
                    //envoid du message
                    $url = $this->generateUrl('app_login', [], urlGeneratorInterface::ABSOLUTE_URL);
                    $entityManager->flush();
                    $departement = $commune->getDepartement();
                    $region = $departement->getRegion();
                    $message = 
                        '<p>  vous etes Maire ' . $id->getNom() . '</p>' .
                        '<p>Le compte pour gérer ce quartier a été  crée avec cette adresse mail ' . $form->get('email')->getData() .
                        '<p>votre mot de passe par défaut est ' . $form->get('plainPassword')->getData() . '. nous vous conseillons 
                         de le réinitialiser une fois que vous avez accés à votre compte</p>
                         <p>accéder à votre compte,
                           en cliquant sur ce lien <a href="' . $url . '">cliquer moi</a></p> ';
                }
                //envoi de mail
                $email = (new Email())
                    ->from('sama.certificat@gmail.com')
                    ->to($form->get('email')->getData())
                    //->cc('cc@example.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    //->priority(Email::PRIORITY_HIGH)
                    ->subject('Information')
                    //->text('Sending emails is fun again!')
                    ->html($message);
    
    
                $mailer->send($email);
    
    
               
            }
    
            return $this->render('admin/ajouterMaire.html.twig', [
                'registrationForm' => $form->createView(), 'idCommune' => $veirifier->getId()
            ]);
        }
        //le maire liste les quartiers sans deleguer pour ajouter un delegue
       #[Route('/admin/lister/communesansmaire', name: 'app_admin_lister_communesansmaire')]
        public function adminListerCommuneSansMaire(PaginatorInterface $paginator, Request $request, PersonneRepository $personneRepository, CommuneRepository $communeRepository)
        {
            //trouvons l'id du commune ou le maire dirige
            $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
            $idDepartement = $tableau[0]['departement_id'];
            // $commune = $communeRepository->find($idcommune);
            $query = $communeRepository->communeSansMaire($idDepartement);
            // dd($query);
            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                10 /*limit per page*/
            );
    
            $pagination->setPageRange(4);
    
            // parameters to template
            return $this->render('admin/listerDepartementSansMaire.html.twig', ['pagination' => $pagination]);
        }

        #[Route('/maire/renommerunquartier/{id}', name: 'app_maire_renommerunquartier')]
    public function maireRenommerUnQuartier(Quartier $id, QuartierRepository $quartierRepository, CommuneRepository $communeRepository, PersonneRepository $personneRepository, Request $request, EntityManagerInterface $entityManager)
    {
        
        
        //verifions si le quartier est bien un quartier du commune pour le maire
        $veirifier = $communeRepository->findOneBy(['id' => $id->getId()]);
        if ($veirifier == null) {
            // $request->getSession()->remove('quartier');
            $this->addFlash('info', ' Attension !vous êtes entrain de faire des modifications d\'url .
                  le quartier choisi ne se trouve pas dans votre commune ');
            return $this->redirectToRoute('app_admin_lister_quartier');
        }
        $quartier = $id;
        $form = $this->createFormBuilder($quartier)
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control', 'required' => ''],
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ vide',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'le nombre de caractére est compris entre [2-50] ',
                        // max length allowed by Symfony for security reasons
                        'max' => 100,
                    ]),
                ],
            ])
            ->add("valider", SubmitType::class, [
                'attr' => ['class' => "w-100 btn btn-success btn-lg border border-white "],
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            //vérifions si le nom de ce quartier existe déja dans son commune
            $existance = $quartierRepository->findBy(['nom' => $form->get('nom')->getData(), 'commune' => $commune]);
            if ($existance != null) {
                //alors le nom de ce quartier esiste deja 
                $this->addFlash('info', 'Attention! le nom de ce quartier existe déjà dans votre commune');
                return $this->render('maire/ajouterquuartier.html.twig', ['form' => $form->createView(), 'quartier' => $quartier]);
            }

            $quartier->setNom($form->get('nom')->getData());
            $entityManager->persist($quartier);
            $entityManager->flush();

            $this->addFlash('success', 'Le nom du quartier est bien modifié');
            return $this->redirectToRoute('app_maire_lister_quartier');
        }



        // parameters to template
        return $this->render('maire/renommerquuartier.html.twig', ['form' => $form->createView(), 'quartier' => $quartier]);
    }
    //le maire liste les delegues
    #[Route('/maire/lister/deleguer', name: 'app_maire_lister_delegue')]
    public function maireListerDelegue(PaginatorInterface $paginator, Request $request, PersonneRepository $personneRepository,  QuartierRepository $quartierRepository)
    {
        //trouvons l'id du commune ou le maire dirige
        $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
        $idcommune = $tableau[0]['commune_id'];
        $query = $quartierRepository->quartierAvecDelegue($idcommune);
        // dd($query);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $pagination->setPageRange(4);

        // parameters to template
        return $this->render('maire/listerdeleguer.html.twig', ['pagination' => $pagination]);
    }
    //le maire remplace un delegue
    #[Route('/admin/remplacermaire/{id}', name: 'app_admin_remplacerMaire')]
    public function maireRemplacerDelegue(Commune $id, CommuneRepository $communeRepository, DepartementRepository $departementRepository, PersonneRepository $personneRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, DemandeinscriptionRepository $demandeinscriptionRepository, MailerInterface $mailer)
    {
        
        //verifions si le quartier est bien un quartier du commune pour le maire
        $veirifier = $communeRepository->findOneBy(['id' => $id->getId()]);


        if ($veirifier == null) {
            // $request->getSession()->remove('quartier');
            $this->addFlash('info', ' Attension !vous êtes entrain de faire des modifications d\'url .
                 le quartier choisi ne se trouve pas dans votre commune ');
            return $this->redirectToRoute('app_maire_lister_delegue');
        }
        //verifions si le quartier a un delegue encours
        if (count($communeRepository->uneCommuneSansMaire($veirifier->getId())) == 0) {
            //donc ce quartier n'a pas de delegue
            $this->addFlash('info', ' Attension !vous êtes entrain de faire des modifications d\'url .
                 le quartier choisi n\'a pas de délégué ');
            return $this->redirectToRoute('app_admin_lister_delegue');
        }
        $form = $this->createForm(AjouterDelegueType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = "";
            //cherchons si l'adresse mail de ce delegue est deja existante dans une autre quartiers
            //c a d si cette personne est deja deleue dans un quartier
            $email = $form->get('email')->getData();
            $maire = $personneRepository->findOneBy(['email' => $email]);
            $ancienMaire = $communeRepository->uneCommuneSansMaire($veirifier->getId());
            $ancienMaire = $personneRepository->find($ancienMaire[0]['personne_id']);
            if ($ancienMaire->getEmail() == $email) {
                $this->addFlash('info', 'inutile de faire ce changement car la personne ayant cette adresse nail est le delegue 
                     actuel de ce quartier');
                return $this->render('admin/remplacermaire.html.twig', [
                    'registrationForm' => $form->createView(), 'idCommune' => $veirifier->getId()
                ]);
            }




            //donc la personne exite dans la base de donnee
            if ($maire != null) {
                if (count($personneRepository->jointureMaireCommune($maire->getId())) != 0) {
                    //donc la personne est actuellemnt delegue dans un quarrtier
                    $this->addFlash('info', ' Attention !la personne ayant comme adresse mail ' . $email .
                        ' est actuellement délégué dans un quartier. vous pouvez l\'ajouter comme délégué avec une autre adresse email ');
                    return $this->render('admin/remplacerdelegue.html.twig', [
                        'registrationForm' => $form->createView(), 'idCommune' => $veirifier->getId()
                    ]);
                }
                if (count($personneRepository->jointuremairecommune($maire->getId())) != 0) {
                    //donc la personne est actuellemnt maire
                    $roles = $maire->getRoles();

                    $this->addFlash('info', ' Attention !la personne ayant comme adresse mail ' . $email .
                        ' est actuellement maire dans une commune .vous pouvez l\'ajouter comme délégué avec une autre adresse email ');
                    return $this->render('admin/remplacerMaire.html.twig', [
                        'registrationForm' => $form->createView(), 'idCommune' => $veirifier->getId()
                    ]);
                }
                $maire->setNom($form->get('nom')->getData());
                $maire->setPreNom($form->get('prenom')->getData());
                $maire->setDatenaissance($form->get('datenaissance')->getData());
                $maire->setLieunaissance($form->get('lieunaissance')->getData());
                $maire->setTelephone($form->get('telephone')->getData());
                $maire->setFonction($form->get('fonction')->getData());
                $roles = ['ROLE_HABITANT', 'ROLE_MAIRE'];
                $maire->setRoles($roles);
                

                //le delgue doit etre donc un habitant de ce quartier
            

                $entityManager->persist($maire);
                $ancienMaire->setDeleguenommeur(null);
                $ancienMaire->setRoles(['ROLE_HABITANT']);
                $entityManager->persist($ancienMaire);
                $communeRepository->mettreFinDegue($id->getId());
                $communeRepository->ajouterUnDelegue($veirifier->getId(), $maire->getId());
                //envoi du message
                //envoi du message
                $url = $this->generateUrl('app_login', [], urlGeneratorInterface::ABSOLUTE_URL);
                $entityManager->flush();
               
                $message = 
                '<p>  vous etes Maire ' . $id->getNom() . '</p>' .
                '<p>Le compte pour gérer ce quartier a été  crée avec cette adresse mail ' . $form->get('email')->getData() .
                '<p>votre mot de passe par défaut est ' . $form->get('plainPassword')->getData() . '. nous vous conseillons 
                 de le réinitialiser une fois que vous avez accés à votre compte</p>
                 <p>accéder à votre compte,
                   en cliquant sur ce lien <a href="' . $url . '">cliquer moi</a></p> ';
            } else {
                $ancienMaire->setDeleguenommeur(null);
                $ancienMaire->setRoles(['ROLE_HABITANT']);
                $entityManager->persist($ancienMaire);
                $communeRepository->mettreFinDegue($id->getId());
                $entityManager->flush();
                //on doit lui creer un compte et tout le reste
                $user = new Personne();
                $user->setNom($form->get('nom')->getData());
                $user->setPreNom($form->get('prenom')->getData());
                $user->setDatenaissance($form->get('datenaissance')->getData());
                $user->setLieunaissance($form->get('lieunaissance')->getData());
                $user->setTelephone($form->get('telephone')->getData());
                $user->setFonction($form->get('fonction')->getData());
                $user->setEmail($form->get('email')->getData());
                $user->setIsVerified(1);
                $roles = ['ROLE_HABITANT', 'ROLE_MAIRE'];
                $user->setRoles($roles);
               
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );


                // $user->setRoles($g);
                //    $user->setDatenaissance(new Date());
                
                $communeRepository->ajouterUnDelegue($veirifier->getId(), $user->getId());
                $entityManager->flush();
                //envoid du message
                $url = $this->generateUrl('app_login', [], urlGeneratorInterface::ABSOLUTE_URL);
                $entityManager->flush();
                
                $message = 
                '<p>  vous etes Maire ' . $id->getNom() . '</p>' .
                '<p>Le compte pour gérer ce quartier a été  crée avec cette adresse mail ' . $form->get('email')->getData() .
                '<p>votre mot de passe par défaut est ' . $form->get('plainPassword')->getData() . '. nous vous conseillons 
                 de le réinitialiser une fois que vous avez accés à votre compte</p>
                 <p>accéder à votre compte,
                   en cliquant sur ce lien <a href="' . $url . '">cliquer moi</a></p> ';
               
            }

            //envoi de mail
            $email = (new Email())
                ->from('sama.certificat@gmail.com')
                ->to($form->get('email')->getData())
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject('Information')
                //->text('Sending emails is fun again!')
                ->html($message);


            $mailer->send($email);
            //email pour l'ancien delegue
            
            $email = (new Email())
                ->from('sama.certificat@gmail.com')
                ->to($ancienMaire->getEmail())
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject('Information')
                //->text('Sending emails is fun again!')
                ->html($message);


            $mailer->send($email);


            $this->addFlash('success', ' changement bien effectué');
            return $this->redirectToRoute('app_maire_lister_delegue');
        }

        return $this->render('admin/remplacermaire.html.twig', [
            'registrationForm' => $form->createView(), 'idCommune' => $veirifier->getId()
        ]);
    }


}
