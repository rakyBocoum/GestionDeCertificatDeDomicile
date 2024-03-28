<?php

namespace App\Controller;


use App\Entity\Demandecretificat;
use DateTimeImmutable;
use App\Entity\Personne;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Form\FournirCertificatType;
use Symfony\Component\Mime\Email;
use App\Entity\Demandeinscription;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use App\Repository\DemandecretificatRepository;
use App\Repository\PersonneRepository;
use App\Repository\QuartierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DemandeinscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Dompdf\Dompdf;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Color\Color;

class DelegueController extends AbstractController
{
    //le delegue liste les demandes d'inscription de son quartier
    #[Route('/delegue/lister/inscription', name: 'app_delegue_lister_inscription')]
    public function delgueListerInscription(PaginatorInterface $paginator, Request $request, DemandeinscriptionRepository $demandeinscriptionRepository, PersonneRepository $personneRepository, QuartierRepository $quartierRepository)
    {
        $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
        //id du quartier ou le delgue dirige
        $idquartier = $tableau[0]['quartier_id'];
        $quartier = $quartierRepository->find($idquartier);
        $query = $demandeinscriptionRepository->findBy(['quartier' => $quartier, 'etatdemande' => 'nonTraiter'], ['datedemande' => 'ASC']);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $pagination->setPageRange(4);

        // parameters to template
        return $this->render('delegue/listerInscription.html.twig', ['pagination' => $pagination]);
    }
    //le delegué observe les détails d'une demande d'inscription
    #[Route('/delegue/details/inscription/{id}', name: 'app_delegue_details_inscription')]
    public function delgueDetailsInscription(Demandeinscription $demandeinscription, PersonneRepository $personneRepository, QuartierRepository $quartierRepository, DemandeinscriptionRepository $demandeinscriptionRepository)
    {
        $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
        //vérifions si la demande d'inscription est pour le délégué
        //id du quartier ou le delgue dirige
        $idquartier = $tableau[0]['quartier_id'];
        $quartier = $quartierRepository->find($idquartier);
        $query = $demandeinscriptionRepository->findOneBy(['id' => $demandeinscription->getId(), 'quartier' => $quartier, 'etatdemande' => 'nonTraiter'], ['datedemande' => 'ASC']);
        if ($query == null) {
            $this->addFlash('info', ' Attention cette demande n\'existe pas dans votre commune ');
            return $this->redirectToRoute('app_delegue_lister_inscription');
        }
        // parameters to template
        return $this->render('delegue/detailsInscription.html.twig', ['demande' => $demandeinscription]);
    }
    //le delegue repond a une demande d'inscription
    #[Route('/delegue/reponse/inscription/{id}/{type}', name: 'app_delegue_reponse_inscription')]
    public function delgueReponseInscription(ManagerRegistry $doctrine, Request $request, Demandeinscription $demandeinscription, $type, MailerInterface $mailer, PersonneRepository $personneRepository, QuartierRepository $quartierRepository, DemandeinscriptionRepository $demandeinscriptionRepository)
    {
        $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
        //vérifions si la demande d'inscription est pour le délégué
        //id du quartier ou le delgue dirige
        $idquartier = $tableau[0]['quartier_id'];
        $quartier = $quartierRepository->find($idquartier);
        $query = $demandeinscriptionRepository->findOneBy(['id' => $demandeinscription->getId(), 'quartier' => $quartier, 'etatdemande' => 'nonTraiter'], ['datedemande' => 'ASC']);
        if ($query == null) {
            $this->addFlash('info', ' Attention cette demande n\'existe pas dans votre commune ');
            return $this->redirectToRoute('app_delegue_lister_inscription');
        }
        // parameters to template

        $email = $demandeinscription->getHabitant()->getEmail();
        $nom = $demandeinscription->getHabitant()->getNom();
        $prenom = $demandeinscription->getHabitant()->getPrenom();
        $message = "<h1> Salut " . $prenom . " " . $nom . ',</h1>';
        if ($type == 'accepter') {
            $demandeinscription->setEtatdemande('accepter');
            $message = $message . "<p>Nous vous informons que votre demande d'inscription au quartier " . $demandeinscription->getQuartier()->getNom() .
                ', commune de ' . $demandeinscription->getQuartier()->getCommune()->getNom() . ', département de ' .
                $demandeinscription->getQuartier()->getCommune()->getDepartement()->getNom() . ", région de " .
                $demandeinscription->getQuartier()->getCommune()->getDepartement()->getRegion()->getNom() .
                " vient d'être acceptée par le délégué actuel de ce  quartier, " . $this->getUser()->getPrenom() . ' ' . $this->getUser()->getNom();
        } elseif ($type == 'refuser') {
            $demandeinscription->setEtatdemande('refuser');
            $message = $message . "<p>Nous vous informons que votre demande d'inscription au quartier " . $demandeinscription->getQuartier()->getNom() .
                ', commune de ' . $demandeinscription->getQuartier()->getCommune()->getNom() . ', département de ' .
                $demandeinscription->getQuartier()->getCommune()->getDepartement()->getNom() . ", région de " .
                $demandeinscription->getQuartier()->getCommune()->getDepartement()->getRegion()->getNom() .
                " vient d'être refusée par le délégué actuel de ce  quartier, " . $this->getUser()->getPrenom() . ' ' . $this->getUser()->getNom();
            $motif = $request->query->get('motif');
            if ($motif == 'di')
                $message = $message . "<p><strong>Motif :</strong> votre dossier est incomplet</P>";
            elseif ($motif == 'pnc')
                $message = $message . "<p><strong>Motif :</strong> Le délégué du quartier ne vous reconnait pas comme étant 
           un habitant de son quartier</P>";
            else
                $message = $message . "<p><strong>Motif :</strong> Le délégué a refusé votre demande pour des raisons personnelles.<br>
           pour plus d'information vous pouvez vous déplacer chez lui(elle)</P>";
        } else
            return $this->redirectToRoute('app_delegue_lister_inscription');


        $email = (new Email())
            ->from('sama.certificat@gmail.com')
            ->to($email)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Réponse à votre demande inscription')
            //->text('Sending emails is fun again!')
            ->html($message);


        $mailer->send($email);

        $demandeinscription->setDatetraitement(new DateTimeImmutable());
        $demandeinscription->setDelegue($this->getUser());
        $entityManager = $doctrine->getManager();
        $entityManager->persist($demandeinscription);
        $entityManager->flush();

        return $this->redirectToRoute('app_delegue_lister_inscription');
    }
    //le delegué retire une personne dans son quartier
    #[Route('/delegue/retirer/inscription/{id}', name: 'app_delegue_retirer_inscription')]
    public function delgueRetirerInscription(EntityManagerInterface $entityManager, Demandeinscription $demandeinscription, PersonneRepository $personneRepository, QuartierRepository $quartierRepository, DemandeinscriptionRepository $demandeinscriptionRepository)
    {

        $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
        //vérifions si la demande d'inscription est pour le délégué
        //id du quartier ou le delgue dirige
        $idquartier = $tableau[0]['quartier_id'];
        $quartier = $quartierRepository->find($idquartier);

        $query = $demandeinscriptionRepository->findOneBy(['id' => $demandeinscription->getId(), 'quartier' => $quartier, 'etatdemande' => 'accepter'], ['datedemande' => 'ASC']);
        if ($query == null) {
            $this->addFlash('info', ' Attention cette personne n\'existe pas dans votre commune ');
            return $this->redirectToRoute('app_mairedelegue_lister_habitant');
        }
        if ($query->getHabitant()->getId() == $this->getUser()->getId()) {
            //donc le delegue veut supprimer lui meme ce qui est impossible
            $this->addFlash('info', ' Impossible car vous êtes le délégué de ce quartier donc vous ếtes normalament un
             habitant de ce quartier');
            return $this->redirectToRoute('app_mairedelegue_lister_habitant');
        }
        $demandeinscription->setEtatdemande('supprimer');
        $entityManager->persist($demandeinscription);
        $entityManager->flush();
        // parameters to template
        $this->addFlash('success', ' La personne est bien retirée de votre quartier');
        return $this->redirectToRoute('app_mairedelegue_lister_habitant');
    }
    //le delegue supprime un habitant
    #[Route('/delegue/supprrimerHabitant', name: 'app_delegue_supprimerhabitant')]
    public function delegueSuppimerHabitant(QuartierRepository $quartierRepository, PersonneRepository $personneRepository, Request $request, EntityManagerInterface $entityManager, DemandeinscriptionRepository $demandeinscriptionRepository): Response
    {

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control', 'required' => '', 'placeholder' => 'sama-domicile@gmail.com', 'type' => 'email'],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Email([
                        'message' => 'adresse mail incorrecte.',
                    ]),
                    new NotBlank([
                        'message' => 'champ vide',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'le nombre de caractére est compris entre [3-180] ',
                        // max length allowed by Symfony for security reasons
                        'max' => 180,
                    ]),
                ],
            ])
            ->add("valider", SubmitType::class, [
                'attr' => ['class' => "w-100 btn btn-success btn-lg border border-white "],
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //recuperons le quartier ou le delegue dirige
            $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
            $idquartier = $tableau[0]['quartier_id'];
            $quartier = $quartierRepository->find($idquartier);
            //cherchons la personne ayant cette adresse mail
            $email = $form->get('email')->getData();
            $personne = $personneRepository->findOneBy(['email' => $email]);
            if ($personne == null) {
                $this->addFlash('info', 'une personne ayant comme adresse mail ' . $email . ' ne se trouve pas dans votre quartier');
                return $this->render('delegue/supprimerHabitant.html.twig', ['form' => $form->createView()]);
            }
            //cherchons si la personne a une demande accepter au quartier du delegue
            $demande = $demandeinscriptionRepository->findOneBy(['Habitant' => $personne, 'quartier' => $quartier, 'etatdemande' => 'accepter']);
            if ($demande == null) {
                $this->addFlash('info', 'une personne ayant comme adresse mail ' . $email . ' ne se trouve pas dans votre quartier');
                return $this->render('delegue/supprimerHabitant.html.twig', ['form' => $form->createView()]);
            }
            //est ce que cette personne est le delegue
            if ($demande->getHabitant()->getId() == $this->getUser()->getId()) {
                //donc le delegue veut supprimer lui meme ce qui est impossible
                $this->addFlash('info', ' Impossible car vous êtes le délégué de ce quartier donc vous ếtes normalament un
                 habitant de ce quartier');
                return $this->render('delegue/supprimerHabitant.html.twig', ['form' => $form->createView()]);
            }
            $demande->setEtatdemande('supprimer');
            $entityManager->persist($demande);
            $entityManager->flush();
            $this->addFlash('success', 'suppression réussi . ' . $personne->getPrenom() . ' ' . $personne->getNom() . ' n\'est plus un habitant de votre quartier');
            return $this->render('delegue/supprimerHabitant.html.twig', ['form' => $form->createView()]);
        }



        // parameters to template
        return $this->render('delegue/supprimerHabitant.html.twig', ['form' => $form->createView()]);
    }
    //le délégué liste les certificat de domicile
    #[Route('/delegue/lister/certificat', name: 'app_delegue_lister_certificat')]
    public function delegueListerCertificat(PaginatorInterface $paginator, Request $request, DemandecretificatRepository $demandecretificatRepository, PersonneRepository $personneRepository, QuartierRepository $quartierRepository)
    {

        $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
        //id du quartier ou le delgue dirige
        $idquartier = $tableau[0]['quartier_id'];
        $quartier = $quartierRepository->find($idquartier);
        $query = $demandecretificatRepository->findBy(['quartier' => $quartier,'etatdemande'=>'encours'], ['etatdemande' => 'DESC', 'datedemande' => 'DESC']);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $pagination->setPageRange(4);

        // parameters to template
        return $this->render('delegue/listerCertificatDomicile.html.twig', ['pagination' => $pagination]);
    }
    private  function imageToBase64($path)
    {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
    //le delegue foiurnit un certificat de domicile
    #[Route('/delegue/fournir/certificat', name: 'app_delegue_fournir_certificat')]
    public function delegueFournirCertificat(Request $request, EntityManagerInterface $entityManager, PersonneRepository $personneRepository, QuartierRepository $quartierRepository)
    {
        $user = new Personne();
        $form = $this->createForm(FournirCertificatType::class, $user, ['require_due_date' => false,]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tableau = $personneRepository->jointuredelguequartier($this->getUser()->getId());
            //vérifions si la demande d'inscription est pour le délégué
            //id du quartier ou le delgue dirige
            $idquartier = $tableau[0]['quartier_id'];
            $quartier = $quartierRepository->find($idquartier);
            $demandeCertificat = new Demandecretificat();
            $demandeCertificat->setDatedemande(new DateTimeImmutable());
            $demandeCertificat->setHabitant($this->getUser());
            $demandeCertificat->setEtatdemande('valide');
            $demandeCertificat->setMontant(200);
            $demandeCertificat->setDelegue($this->getUser());
            $demandeCertificat->setIdQrcode(crypt('defaut', '$1$rasmusle$'));
            $demandeCertificat->setQuartier($quartier);
            $entityManager->persist($demandeCertificat);
            $entityManager->flush();
            $idqrcode = "" . $demandeCertificat->getId();
            $idqrcode = crypt($idqrcode, '$1$rasmusle$');
            $demandeCertificat->setIdQrcode($idqrcode);
            $entityManager->persist($demandeCertificat);
            $entityManager->flush();
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
                'personne' => $user, 'image' => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/logoCertificat.png'), 'qrcode' => $qrcodeimage, 'region' => $region, 'departement' => $departement, 'commune' => $commune, 'quartier' => $quartier, 'delegue' => $delegue, 'demande' => $demandeCertificat
            ];
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
        }



        return $this->render('delegue/fournircertificat.html.twig', ['registrationForm' => $form->createView()]);
    }
    //le deledgue etudie la deamnde de  certificat
    #[Route('/delegue/etude/certificat/{type}/{id}', name: 'app_delegue_reponse_certificat')]
    public function delegueEtudeCertificat(EntityManagerInterface $entityManager,$type,DemandecretificatRepository $demandecretificatRepository, Demandecretificat $id)
    {

        $certifcat=$id;
       $certifcat->setEtatdemande($type);
      $entityManager->persist($certifcat);
      $entityManager->flush();
        // parameters to template
        return $this->redirectToRoute('app_delegue_lister_certificat');
    }
}
