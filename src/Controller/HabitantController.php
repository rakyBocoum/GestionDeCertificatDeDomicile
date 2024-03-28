<?php

namespace App\Controller;



use App\Form\DemandeInscription1Type;
use App\Form\DemandeInscription2Type;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Persistence\ManagerRegistry;


use Dompdf\Dompdf;
use DateTimeImmutable;

use Endroid\QrCode\QrCode;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Label;

use App\Entity\Demandecretificat;

use App\Entity\Demandeinscription;

use Endroid\QrCode\Writer\PngWriter;

use Endroid\QrCode\Encoding\Encoding;
use App\Repository\PersonneRepository;
use App\Repository\QuartierRepository;
use Endroid\QrCode\Label\Font\NotoSans;
use Doctrine\ORM\EntityManagerInterface;

use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;

use Laminas\Code\Generator\GeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\DemandecretificatRepository;

use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DemandeinscriptionRepository;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HabitantController extends AbstractController
{
    //l'habitant liste ses demandes d'inscription
    #[Route('/habitant/lister/inscription', name: 'app_habitant_lister_inscription')]
    public function habitantListerInscription(PaginatorInterface $paginator, Request $request, DemandeinscriptionRepository $demandeinscriptionRepository)
    {
        $query = $demandeinscriptionRepository->findBy(['Habitant' => $this->getUser()], ['datetraitement' => 'DESC', 'datedemande' => 'ASC']);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $pagination->setPageRange(4);

        // parameters to template
        return $this->render('habitant/listerInscription.html.twig', ['pagination' => $pagination]);
    }
    //l'habitant liste ses residences
    #[Route('/habitant/lister/residences', name: 'app_habitant_lister_residences')]
    public function habitantListerResidences(PaginatorInterface $paginator, Request $request, DemandeinscriptionRepository $demandeinscriptionRepository)
    {
        $query = $demandeinscriptionRepository->findBy(['Habitant' => $this->getUser(), 'etatdemande' => 'accepter'], ['datetraitement' => 'DESC']);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $pagination->setPageRange(4);

        // parameters to template
        return $this->render('habitant/listerRecidences.html.twig', ['pagination' => $pagination]);
    }
    //l'habitant quitte une residence
    #[Route('/habitant/rquitter/resience/{id}', name: 'app_habitant_quitter_residence')]
    public function habitantQuiterResidence(EntityManagerInterface $entityManager, Demandeinscription $demandeinscription, PersonneRepository $personneRepository,  DemandeinscriptionRepository $demandeinscriptionRepository)
    {

        //vérifions si la demande d'inscription est pour l'habitant
        //c a d la personne a une demande d'inscription accepter

        $query = $demandeinscriptionRepository->findBy(['id' => $demandeinscription->getId(), 'Habitant' => $this->getUser(), 'etatdemande' => 'accepter']);
        if ($query == null) {
            $this->addFlash('info', ' Attention Vous êtes entrain  de faire queleque chose d\'illicite');
            return $this->redirectToRoute('app_habitant_lister_residences');
        }
        if (count($personneRepository->jointuredelguequartier($this->getUser()->getId())) != 0) {
            //donc la personne est actuellemnt delegue dans un quarrtier
            //verifions si ce quartier corespond au quartier qu'il dirige
            // $tab
            $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
            //id du quartier ou le delgue dirige
            $idquartier = $tableau[0]['quartier_id'];
            if ($idquartier == $demandeinscription->getQuartier()->getId()) {
                $this->addFlash('info', ' Impossible de quitter ce quartier car vous êtes le délégué de ce quartier donc vous ếtes normalament un
                    habitant de ce quartier');
                return $this->redirectToRoute('app_habitant_lister_residences');
            }
        }
        $demandeinscription->setEtatdemande('annuler');
        $entityManager->persist($demandeinscription);
        $entityManager->flush();
        // parameters to template
        $this->addFlash('success', ' Désormais vous n\'êtes plus un habitant au quartier ' . $demandeinscription->getQuartier()->getNom());
        return $this->redirectToRoute('app_habitant_lister_residences');
    }
    //l'habitant liste ses demandes de certificats
    #[Route('/habitant/lister/certificat', name: 'app_habitant_lister_certificat')]
    public function habitantListerCertificat(PaginatorInterface $paginator, Request $request, DemandecretificatRepository $demandecretificatRepository)
    {
        $query = $demandecretificatRepository->findBy(['Habitant' => $this->getUser(), 'etatdemande' => 'accepter'], ['etatdemande' => 'DESC', 'datedemande' => 'DESC']);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $pagination->setPageRange(4);

        // parameters to template
        return $this->render('habitant/listerCertficat.html.twig', ['pagination' => $pagination]);
    }
    //habitant fait une demande de certificat
    #[Route('/habitant/demande/certificat/{id}', name: 'app_habitant_demandes_certificat')]
    public function habitantDemandeDeCertificat(Request $request, PaginatorInterface $paginator, QuartierRepository $quartierRepository, EntityManagerInterface $entityManager, Demandeinscription $demandeinscription = null, DemandeinscriptionRepository $demandeinscriptionRepository, PersonneRepository $personneRepository)
    {

        //vérifions si la demande d'inscription est pour l'habitant
        //c a d la personne a une demande d'inscription accepter
        if ($demandeinscription == null) {
            $query = $demandeinscriptionRepository->findBy(['Habitant' => $this->getUser(), 'etatdemande' => 'accepter'], ['datetraitement' => 'DESC']);

            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                10 /*limit per page*/
            );

            $pagination->setPageRange(4);

            // parameters to template
            return $this->render('habitant/demandeDeCerticat.html.twig', ['pagination' => $pagination]);
        } else {
            $query = $demandeinscriptionRepository->findBy(['id' => $demandeinscription->getId(), 'Habitant' => $this->getUser(), 'etatdemande' => 'accepter']);
            if ($query == null) {
                $this->addFlash('info', ' Attention Vous êtes entrain  de faire queleque chose d\'illicite');
                return $this->redirectToRoute('app_habitant_lister_certificat');
            }
            if (count($personneRepository->jointuredelguequartier($this->getUser()->getId())) != 0) {
                //donc la personne est actuellemnt delegue dans un quarrtier
                //verifions si ce quartier corespond au quartier qu'il dirige
                // $tab
                $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
                //id du quartier ou le delgue dirige
                $idquartier = $tableau[0]['quartier_id'];
                if ($idquartier == $demandeinscription->getQuartier()->getId()) {
                    $this->addFlash('info', ' Impossible  car vous êtes le délégué de ce quartier');
                    $query = $demandeinscriptionRepository->findBy(['Habitant' => $this->getUser(), 'etatdemande' => 'accepter'], ['datetraitement' => 'DESC']);

                    $pagination = $paginator->paginate(
                        $query, /* query NOT result */
                        $request->query->getInt('page', 1), /*page number*/
                        10 /*limit per page*/
                    );

                    $pagination->setPageRange(4);

                    // parameters to template
                    return $this->render('habitant/demandeDeCerticat.html.twig', ['pagination' => $pagination]);
                }
            }
            //cherchons le delegue en cours dans le quartier
            $tableau = $quartierRepository->unQuartierSansDelegue($demandeinscription->getQuartier()->getId());
            $delegueActuel = $personneRepository->find($tableau[0]['personne_id']);
            $demandeCertificat = new Demandecretificat();
            $demandeCertificat->setDatedemande(new DateTimeImmutable());
            $demandeCertificat->setHabitant($this->getUser());
            $demandeCertificat->setEtatdemande('encours');
            $demandeCertificat->setMontant(200);
            $demandeCertificat->setDelegue($delegueActuel);
            $demandeCertificat->setIdQrcode(crypt('defaut', '$1$rasmusle$'));
            $demandeCertificat->setQuartier($demandeinscription->getQuartier());
            $demandeCertificat->setDemandeInscription($demandeinscription);
            $entityManager->persist($demandeCertificat);
            $entityManager->flush();
            $idqrcode = "" . $demandeCertificat->getId();
            $idqrcode = crypt($idqrcode, '$1$rasmusle$');
            $demandeCertificat->setIdQrcode($idqrcode);
            $entityManager->persist($demandeCertificat);
            $entityManager->flush();
            return $this->redirectToRoute('app_habitant_lister_certificat');
        }
    }
    //l'habiatnt télécharge le certificat de domicile
    #[Route('/habitant/telecharger/certificat/{id}', name: 'app_habitant_telecharger_certificat')]
    public function habitantTelechcargerCertificat( EntityManagerInterface $entityManager,Demandecretificat $demandeCertificat, DemandecretificatRepository $demandecretificatRepository)
    {
        $query = $demandecretificatRepository->findOneBy(['Habitant' => $this->getUser(), 'etatdemande' => 'accepter', 'id' => $demandeCertificat->getId()]);


        

        if ($query == null) {
            $this->addFlash('info', ' Ce certificat a déjà été téléchargé');
            return $this->redirectToRoute('app_habitant_lister_certificat');
        }
       // die('erreur');
        $writer = new PngWriter();

        $url = $this->generateUrl('app_maire_verification_certificat', ['idQrcode' => $demandeCertificat->getIdQrcode()], urlGeneratorInterface::ABSOLUTE_URL);

        $qrCode = QrCode::create($url)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(120)
            ->setMargin(0)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        $label = Label::create('')->setFont(new NotoSans(8));
        $qrcodeimage = $qrCodes['simple'] = $writer->write($qrCode, null, $label->setText('Utilisable une seule fois'))->getDataUri();
        $qrCode->setForegroundColor(new Color(255, 0, 0));
        $quartier = $demandeCertificat->getQuartier();
        $commune = $quartier->getCommune();
        $departement = $commune->getDepartement();
        $region = $departement->getRegion();
        $delegue = $demandeCertificat->getDelegue();
        $data = [
            'personne' => $this->getUser(), 'image' => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/logoCertificat.png'), 'qrcode' => $qrcodeimage, 'region' => $region, 'departement' => $departement, 'commune' => $commune, 'quartier' => $quartier, 'delegue' => $delegue, 'demande' => $demandeCertificat
        ];
        $demandeCertificat=$demandeCertificat;
        $demandeCertificat->setEtatdemande('valide');
        $entityManager->persist($demandeCertificat);
        $entityManager->flush();

        $html =  $this->renderView('habitant/telechargerCertficat.html.twig', $data);
        $dompdf = new Dompdf();
        //  $options = $dompdf->getOptions();
        //      $options->setDefaultFont('Courier');
        //    $dompdf->setOptions($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return new Response(
            $dompdf->stream('resume', ["Attachement" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
        // parameters to template
        // return $this->render('habitant/telechargerCertficat.html.twig');
    }
    private  function imageToBase64($path)
    {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
    //l'habitant fait une demande d'inscription
    #[Route('/habitant/demande/inscription', name: 'app_demande_inscription')]
    public function index(Request $request, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();

        $form = $this->createForm(DemandeInscription1Type::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $session->set('departement', $form->get('departement')->getData());
            return $this->redirect('/habitant/demande/inscription2');
        }
        return $this->render('demande_inscription/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/habitant/demande/inscription2', name: 'app_demande_inscription2')]
    public function etape2(Request $request, RequestStack $requestStack, ManagerRegistry $doctrine): Response
    {
        $form1 = $this->createForm(DemandeInscription2Type::class);
        $form1->handleRequest($request);
        $quartier = $form1->get('quartier')->getData();
        if ($form1->isSubmitted() && $form1->isValid()) {
            $quartier = $form1->get('quartier')->getData();
            
            $verification = $doctrine->getRepository(Demandeinscription::class)->findBy(['Habitant' => $this->getUser(), 'quartier' => $quartier, 'etatdemande' => 'nonTraiter']);
             $verification1 = $doctrine->getRepository(Demandeinscription::class)->findBy(['Habitant' => $this->getUser(), 'quartier' => $quartier, 'etatdemande' => 'accepter']);
                        
             if ($verification == null && $verification1==null) {
                $requestStack->getSession()->remove('departement');
                $photo = $form1['photo']->getData();
                $facture = $form1['facture']->getData();
                $demandeinscription = new Demandeinscription();
                $demandeinscription->setDatedemande(new DateTimeImmutable());
                $demandeinscription->setHabitant($this->getUser());
                $demandeinscription->setQuartier($quartier);
                $demandeinscription->setEtatdemande('nonTraiter');
               // $iduser = $this->getUser()->getId();
                //$idquartier = $quartier->getId();
                $extensionphoto = $photo->guessExtension();
                $extensionfacture = $facture->guessExtension();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($demandeinscription);

                $entityManager->flush();
                $facture->move("facture", $demandeinscription->getId() . '.' . $extensionfacture);
                $photo->move("imagerecent", $demandeinscription->getId() . '.' . $extensionphoto);
                $this->addFlash(
                    'success',
                    'votre inscription a été faite avec succés. vous serez informez par mail dés que le délégué du quartier a étudié votre dossier vous pouvez aussi revenir sur votre compte pou voir l\'état de traitement de votre demande'
                );
                return $this->redirect('/habitant/demande/inscription');
            } else {
                $commune = $quartier->getCommune();
                $departement = $commune->getDepartement();
                $region = $departement->getRegion();
                $info="";
                if($verification!=null)
                $info = 'nous vous informons que vous avez une demande en cours de traitement au quartier ' . $quartier->getNom() . ' ,commune de ' . $commune->getNom() . ' ,département de ' . $departement->getNom() . ' ,région de ' . $region->getNom();
                else
                 $info = 'nous vous informons que vous avez une demande acceptée au quartier ' . $quartier->getNom() . ' ,commune de ' . $commune->getNom() . ' ,département de ' . $departement->getNom() . ' ,région de ' . $region->getNom().'. donc 
                 inutile de faire une  demande dans ce quartier';
                $this->addFlash(
                    'notice',
                    $info
                );
            }
        }
        return $this->render('demande_inscription/etape2.html.twig', [
            'form' => $form1->createView()
        ]);
    }
}
