<?php

namespace App\Controller;


use App\Form\ModifierCompteType;

use App\Repository\PersonneRepository;
use App\Repository\QuartierRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DemandeinscriptionRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Dompdf\Dompdf;





class PrincipalController extends AbstractController
{
    #[Route('/habitant/principal', name: 'app_principal')]
    public function index(): Response
    {
         if($this->isGranted('ROLE_ADMIN'))
         {
         return $this->redirectToRoute('admin');
         }
         else
         {
        return $this->render('principal/index.html.twig', [
            'controller_name' => 'PrincipalController',
        ]);
    }
    }
    //modification de son compte
    #[Route('/habitant/modifiercompte', name: 'app_modifiercompte')]
    public function modifierCompte(UserPasswordHasherInterface $userPasswordHasher, Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ModifierCompteType::class, $user, ['require_due_date' => false,]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('plainPassword')->getData())
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->render('principal/index.html.twig', [
                'controller_name' => 'PrincipalController',
            ]);
        }
        return $this->render('principal/modifiercompte.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    //le maire ou le delegue liste les habitants
    #[Route('/mairdelegue/lister/habitant', name: 'app_mairedelegue_lister_habitant')]
    public function maireDeleGueListerHabitant(PaginatorInterface $paginator, Request $request, DemandeinscriptionRepository $demandeinscriptionRepository, PersonneRepository $personneRepository, QuartierRepository $quartierRepository)
    {
        $query = null;
        if ($this->isGranted('ROLE_MAIRE')) {
            $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
            $idcommune = $tableau[0]['commune_id'];
            $query = $demandeinscriptionRepository->enembleHabitantCommune($idcommune);
        } elseif ($this->isGranted('ROLE_DELEGUE')) {
            $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
            //id du quartier ou le delgue dirige
            $idquartier = $tableau[0]['quartier_id'];
            $quartier = $quartierRepository->find($idquartier);
            $query = $demandeinscriptionRepository->findBy(['quartier' => $quartier, 'etatdemande' => 'accepter'], ['datedemande' => 'ASC']);
        } else {
            return $this->redirectToRoute('app_login');
        }

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $pagination->setPageRange(4);

        // parameters to template
        return $this->render('principal/mairedelegueListerHabitant.html.twig', ['pagination' => $pagination]);
    }
    //le maire ou le delgue recherche des personnes 
    #[Route('/oumairdelegue/rechercher/habitant', name: 'app_mairedelegue_rechercher_habitant')]
    public function maireDeleGueRechercherHabitant(PaginatorInterface $paginator, Request $request, DemandeinscriptionRepository $demandeinscriptionRepository, PersonneRepository $personneRepository)
    {
        $query = null;
        $info=$request->query->get('info');
        
        if ($this->isGranted('ROLE_MAIRE')) 
        {
            if($info==null){
                return $this->redirectToRoute('app_principal');
            }
            $info=trim($info);
            $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
            $idcommune = $tableau[0]['commune_id'];
            $query = $demandeinscriptionRepository->rechercherUnePersonneCommune($idcommune,$info);
        } 
        elseif ($this->isGranted('ROLE_DELEGUE')) {
            if($info==null)
            {
                return $this->redirectToRoute('app_principal');
            }
            $info=trim($info);
            $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
            //id du quartier ou le delgue dirige
            $idquartier = $tableau[0]['quartier_id'];
            $query = $demandeinscriptionRepository->rechercherUnePersonneQuartier($idquartier,$info);
        } 
        else 
        {
            return $this->redirectToRoute('app_login');
        }


        // parameters to template
        return $this->render('principal/mairedelegueRechercherHabitant.html.twig', ['pagination' => $query]);
    }

    private  function imageToBase64($path)
    {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

    //le maire ou le delegue Telecharge la  liste les habitants
    #[Route('/mairdelegue/telecharger/habitant', name: 'app_mairedelegue_telecharger_habitant')]
    public function maireDeleGuetelechargerHabitant(PaginatorInterface $paginator, Request $request, DemandeinscriptionRepository $demandeinscriptionRepository, PersonneRepository $personneRepository, QuartierRepository $quartierRepository)
    {
        $query = null;
        if ($this->isGranted('ROLE_MAIRE')) {
            $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
            $idcommune = $tableau[0]['commune_id'];
            $query = $demandeinscriptionRepository->enembleHabitantCommune($idcommune);
        } elseif ($this->isGranted('ROLE_DELEGUE')) {
            $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
            //id du quartier ou le delgue dirige
            $idquartier = $tableau[0]['quartier_id'];
            $quartier = $quartierRepository->find($idquartier);
            $query = $demandeinscriptionRepository->findBy(['quartier' => $quartier, 'etatdemande' => 'accepter'], ['datedemande' => 'ASC']);
        } else {
            return $this->redirectToRoute('app_login');
        }

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );


        $data = ['pagination' => $pagination, 'image' => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/image/logo.jpg')];
        $html =  $this->renderView('principal/telechargerhabitant.html.twig', $data);
        $dompdf = new Dompdf();
        //  $options = $dompdf->getOptions();
        //      $options->setDefaultFont('Courier');
        //    $dompdf->setOptions($options);
        $dompdf->loadHtml($html);
        //$dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return new Response(
            $dompdf->stream('Fichier', ["Attachement" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }
}
