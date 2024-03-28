<?php

namespace App\Controller;



use DateTimeImmutable;
use App\Entity\Personne;
use App\Entity\Quartier;
use App\Form\AjouterDelegueType;

use Symfony\Component\Mime\Email;
use App\Entity\Demandeinscription;
use App\Repository\CommuneRepository;
use App\Repository\DemandecretificatRepository;
use App\Repository\PersonneRepository;
use App\Repository\QuartierRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;

use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;

use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DemandeinscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class MaireController extends AbstractController
{
    //le maire liste ses quartiers de son commune
    #[Route('/maire/lister/quartier', name: 'app_maire_lister_quartier')]
    public function maireListerQuartier(PaginatorInterface $paginator, Request $request, PersonneRepository $personneRepository, CommuneRepository $communeRepository, QuartierRepository $quartierRepository)
    {
        //trouvons l'id du commune ou le maire dirige
        $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
        $idcommune = $tableau[0]['commune_id'];
        $commune = $communeRepository->find($idcommune);
        $query = $quartierRepository->findBy(['commune' => $commune], ['nom' => 'ASC']);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $pagination->setPageRange(4);

        // parameters to template
        return $this->render('maire/listerQuartier.html.twig', ['pagination' => $pagination]);
    }
    //le maire ajoute un delegue dans un quartier au niveau de son commune
    //un quartier n'ayant pas de delegue
    #[Route('/maire/ajouterundelegue/{id}', name: 'app_maire_ajouterdelegue')]
    public function maireAjouterDelegue(Quartier $id, QuartierRepository $quartierRepository, CommuneRepository $communeRepository, PersonneRepository $personneRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, DemandeinscriptionRepository $demandeinscriptionRepository, MailerInterface $mailer)
    {
        //dd($id);
        //trouvons la commune du maire
        $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
        $idcommune = $tableau[0]['commune_id'];
        $commune = $communeRepository->find($idcommune);
        //verifions si le quartier est bien un quartier de la commune pour le maire
        $veirifier = $quartierRepository->findOneBy(['id' => $id->getId(), 'commune' => $commune]);


        if ($veirifier == null) {
            // $request->getSession()->remove('quartier');
            $this->addFlash('info', ' Attention !vous êtes entrain de faire des modifications d\'url .
                 le quartier choisit ne se trouve pas dans votre commune ');
            return $this->redirectToRoute('app_maire_lister_quartier');
        }
        //verifions si le quartier a un delegue encours
        if (count($quartierRepository->unQuartierSansDelegue($veirifier->getId())) != 0) {
            //donc ce quartier a deja un delgue impossible d'ajouter une autre
            $this->addFlash('info', ' Attention !ce quartier a déjà un délégué dont son mandat est en cours. Donc impossible 
                d\'ajouter un autre néanmoins vous avez la possibilité de le remplacer . ');
            return $this->redirectToRoute('app_maire_lister_quartier');
        }
        $form = $this->createForm(AjouterDelegueType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = "";
            //cherchons si l'adresse mail de ce delegue est deja existante dans une autre quartiers
            //c a d si cette personne est deja delegue dans un quartier
            $email = $form->get('email')->getData();
            $delegue = $personneRepository->findOneBy(['email' => $email]);

            //donc la personne exite dans la base de donnee
            if ($delegue != null) {
                if (count($personneRepository->jointuredelguequartier($delegue->getId())) != 0) {
                    //donc la personne est actuellemnt maire dans une commune
                    $this->addFlash('info', ' Attention !la personne ayant comme adresse mail ' . $email .
                        ' est actuellement délégué dans un quartier. vous pouvez l\'ajouter comme délégué avec une autre adresse email ');
                    return $this->render('maire/ajouterdelegue.html.twig', [
                        'registrationForm' => $form->createView(),
                    ]);
                }
                if (count($personneRepository->jointuremairecommune($delegue->getId())) != 0) {
                    //donc la personne est actuellemnt maire
                    $roles = $delegue->getRoles();

                    $this->addFlash('info', ' Attention !la personne ayant comme adresse mail ' . $email .
                        ' est actuellement maire dans une commune .vous pouvez l\'ajouter comme délégué avec une autre adresse email ');
                    return $this->render('maire/ajouterdelegue.html.twig', [
                        'registrationForm' => $form->createView(),
                    ]);
                }
                $delegue->setNom($form->get('nom')->getData());
                $delegue->setPreNom($form->get('prenom')->getData());
                $delegue->setDatenaissance($form->get('datenaissance')->getData());
                $delegue->setLieunaissance($form->get('lieunaissance')->getData());
                $delegue->setTelephone($form->get('telephone')->getData());
                $delegue->setFonction($form->get('fonction')->getData());
                $roles = ['ROLE_HABITANT', 'ROLE_DELEGUE'];
                $delegue->setRoles($roles);
                $delegue->setDeleguenommeur($this->getUser());

                //le delgue doit etre donc un habitant de ce quartier
                $deleguedemandenontraiter = $demandeinscriptionRepository->findOneBy(['Habitant' => $delegue, 'etatdemande' => 'nonTraiter', 'quartier' => $veirifier]);
                $deleguedemandeaccepter = $demandeinscriptionRepository->findOneBy(['Habitant' => $delegue, 'etatdemande' => 'accepter', 'quartier' => $veirifier]);
                //donc le personne n'est pas un habitant de ce quartier
                if ($deleguedemandeaccepter == null && $deleguedemandenontraiter == null) {
                    $demandeinscription = new Demandeinscription();
                    $demandeinscription->setDatedemande(new DateTimeImmutable());
                    $demandeinscription->setDatetraitement(new DateTimeImmutable());
                    $demandeinscription->setHabitant($delegue);
                    $demandeinscription->setDelegue($delegue);
                    $demandeinscription->setQuartier($veirifier);
                    $demandeinscription->setEtatdemande('accepter');
                    $entityManager->persist($demandeinscription);
                } else {
                    if ($deleguedemandenontraiter != null) {
                        $deleguedemandenontraiter->setEtatdemande('accepter');
                        $deleguedemandenontraiter->setDatetraitement(new DateTimeImmutable());
                        $deleguedemandenontraiter->setDelegue($delegue);
                        $entityManager->persist($deleguedemandenontraiter);
                    }
                }

                $entityManager->persist($delegue);
                $quartierRepository->ajouterUnDelegue($veirifier->getId(), $delegue->getId());
                //envoi du message
                $url = $this->generateUrl('app_login', [], urlGeneratorInterface::ABSOLUTE_URL);
                $entityManager->flush();
                $departement = $commune->getDepartement();
                $region = $departement->getRegion();
                $message = "<h1> Salut " . $delegue->getPrenom() . " " . $delegue->getNom() . "</h1>" .
                    '<p>le maire ' . $this->getUser()->getPrenom() . ' ' . $this->getUser()->getNom() .
                    ' du commune de ' . $commune->getNom() . ', département de ' . $departement->getNom() . ', région de ' .
                    $region->getNom() . ' vous a nommé comme délégué au quartier ' . $id->getNom() . '</p>' .
                    '<p>Le compte pour gérer ce quartier est le même que vous avez créer avec cette adresse mail ' . $delegue->getEmail() .
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
                $roles = ['ROLE_HABITANT', 'ROLE_DELEGUE'];
                $user->setRoles($roles);
                $user->setDeleguenommeur($this->getUser());
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
                $quartierRepository->ajouterUnDelegue($veirifier->getId(), $user->getId());
                $entityManager->flush();
                //envoid du message
                $url = $this->generateUrl('app_login', [], urlGeneratorInterface::ABSOLUTE_URL);
                $entityManager->flush();
                $departement = $commune->getDepartement();
                $region = $departement->getRegion();
                $message = "<h1> Salut " . $user->getPrenom() . " " . $user->getNom() . "</h1>" .
                    '<p>le maire ' . $this->getUser()->getPrenom() . ' ' . $this->getUser()->getNom() .
                    ' du commune de ' . $commune->getNom() . ', département de ' . $departement->getNom() . ', région de ' .
                    $region->getNom() . ' vous a nommé comme délégué au quartier ' . $id->getNom() . '</p>' .
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


            $this->addFlash('success', ' l\'ajout est fait avec succés ');
            return $this->redirectToRoute('app_maire_lister_quartier');
        }

        return $this->render('maire/ajouterdelegue.html.twig', [
            'registrationForm' => $form->createView(), 'idquartier' => $veirifier->getId()
        ]);
    }
    //le maire liste les quartiers sans deleguer pour ajouter un delegue
    #[Route('/maire/lister/quartiersansdelegue', name: 'app_maire_lister_quartiersansdelegue')]
    public function maireListerQuartierSansDelegue(PaginatorInterface $paginator, Request $request, PersonneRepository $personneRepository, QuartierRepository $quartierRepository)
    {
        //trouvons l'id du commune ou le maire dirige
        $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
        $idcommune = $tableau[0]['commune_id'];
        // $commune = $communeRepository->find($idcommune);
        $query = $quartierRepository->quartierSansDelegue($idcommune);
        // dd($query);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $pagination->setPageRange(4);

        // parameters to template
        return $this->render('maire/listerQuartierSansDelegue.html.twig', ['pagination' => $pagination]);
    }
    //le maire ajoute un quartier
    #[Route('/maire/ajouterunquartier', name: 'app_maire_ajouterunquartier')]
    public function maireAjouterUnQuartier(Request $request, PersonneRepository $personneRepository, CommuneRepository $communeRepository, EntityManagerInterface $entityManager, QuartierRepository $quartierRepository)
    {

        $quartier = new Quartier();
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
                'attr' => ['class' => "w-100 btn  btn-lg ",'style'=>"background-color:#4B0082;color:#ffff;"],
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            //vérifions si le nom de ce quartier existe déja dans son commune
            //trouvons l'id du commune ou le maire dirige

            $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
            $idcommune = $tableau[0]['commune_id'];
            $commune = $communeRepository->find($idcommune);
            $existance = $quartierRepository->findBy(['nom' => $form->get('nom')->getData(), 'commune' => $commune]);
            if ($existance != null) {
                //alors le nom de ce quartier esiste deja 
                $this->addFlash('info', 'Attention! le nom de ce quartier existe déjà dans votre commune');
                return $this->render('maire/ajouterquuartier.html.twig', ['form' => $form->createView(),]);
            }

            $quartier->setNom($form->get('nom')->getData());
            $quartier->setCommune($commune);
            $entityManager->persist($quartier);
            $entityManager->flush();

            $this->addFlash('success', 'Le nouveau quartier est bien ajouté. Nous vous conseillons d\'ajouter un nouveau délégué pour ce quartier ');
            return $this->render('maire/ajouterquuartier.html.twig', ['form' => $form->createView(),]);
        }



        // parameters to template
        return $this->render('maire/ajouterquuartier.html.twig', ['form' => $form->createView(),]);
    }
    //le maire renomme un quartier de son commune
    #[Route('/maire/renommerunquartier/{id}', name: 'app_maire_renommerunquartier')]
    public function maireRenommerUnQuartier(Quartier $id, QuartierRepository $quartierRepository, CommuneRepository $communeRepository, PersonneRepository $personneRepository, Request $request, EntityManagerInterface $entityManager)
    {
        //dd($id);
        //trouvons la commune du maire
        $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
        $idcommune = $tableau[0]['commune_id'];
        $commune = $communeRepository->find($idcommune);
        //verifions si le quartier est bien un quartier du commune pour le maire
        $veirifier = $quartierRepository->findOneBy(['id' => $id->getId(), 'commune' => $commune]);
        if ($veirifier == null) {
            // $request->getSession()->remove('quartier');
            $this->addFlash('info', ' Attension !vous êtes entrain de faire des modifications d\'url .
                  le quartier choisi ne se trouve pas dans votre commune ');
            return $this->redirectToRoute('app_maire_lister_quartier');
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
    #[Route('/maire/remplacerdelegue/{id}', name: 'app_maire_remplacerdelegue')]
    public function maireRemplacerDelegue(Quartier $id, QuartierRepository $quartierRepository, CommuneRepository $communeRepository, PersonneRepository $personneRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, DemandeinscriptionRepository $demandeinscriptionRepository, MailerInterface $mailer)
    {
        //dd($id);
        //trouvons la commune du maire
        $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
        $idcommune = $tableau[0]['commune_id'];
        $commune = $communeRepository->find($idcommune);
        //verifions si le quartier est bien un quartier du commune pour le maire
        $veirifier = $quartierRepository->findOneBy(['id' => $id->getId(), 'commune' => $commune]);


        if ($veirifier == null) {
            // $request->getSession()->remove('quartier');
            $this->addFlash('info', ' Attension !vous êtes entrain de faire des modifications d\'url .
                 le quartier choisi ne se trouve pas dans votre commune ');
            return $this->redirectToRoute('app_maire_lister_delegue');
        }
        //verifions si le quartier a un delegue encours
        if (count($quartierRepository->unQuartierSansDelegue($veirifier->getId())) == 0) {
            //donc ce quartier n'a pas de delegue
            $this->addFlash('info', ' Attension !vous êtes entrain de faire des modifications d\'url .
                 le quartier choisi n\'a pas de délégué ');
            return $this->redirectToRoute('app_maire_lister_delegue');
        }
        $form = $this->createForm(AjouterDelegueType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = "";
            //cherchons si l'adresse mail de ce delegue est deja existante dans une autre quartiers
            //c a d si cette personne est deja deleue dans un quartier
            $email = $form->get('email')->getData();
            $delegue = $personneRepository->findOneBy(['email' => $email]);
            $ancienDelgue = $quartierRepository->unQuartierSansDelegue($veirifier->getId());
            $ancienDelgue = $personneRepository->find($ancienDelgue[0]['personne_id']);
            if ($ancienDelgue->getEmail() == $email) {
                $this->addFlash('info', 'inutile de faire ce changement car la personne ayant cette adresse nail est le delegue 
                     actuel de ce quartier');
                return $this->render('maire/remplacerdelegue.html.twig', [
                    'registrationForm' => $form->createView(), 'idquartier' => $veirifier->getId()
                ]);
            }




            //donc la personne exite dans la base de donnee
            if ($delegue != null) {
                if (count($personneRepository->jointuredelguequartier($delegue->getId())) != 0) {
                    //donc la personne est actuellemnt delegue dans un quarrtier
                    $this->addFlash('info', ' Attention !la personne ayant comme adresse mail ' . $email .
                        ' est actuellement délégué dans un quartier. vous pouvez l\'ajouter comme délégué avec une autre adresse email ');
                    return $this->render('maire/remplacerdelegue.html.twig', [
                        'registrationForm' => $form->createView(), 'idquartier' => $veirifier->getId()
                    ]);
                }
                if (count($personneRepository->jointuremairecommune($delegue->getId())) != 0) {
                    //donc la personne est actuellemnt maire
                    $roles = $delegue->getRoles();

                    $this->addFlash('info', ' Attention !la personne ayant comme adresse mail ' . $email .
                        ' est actuellement maire dans une commune .vous pouvez l\'ajouter comme délégué avec une autre adresse email ');
                    return $this->render('maire/remplacerdelegue.html.twig', [
                        'registrationForm' => $form->createView(), 'idquartier' => $veirifier->getId()
                    ]);
                }
                $delegue->setNom($form->get('nom')->getData());
                $delegue->setPreNom($form->get('prenom')->getData());
                $delegue->setDatenaissance($form->get('datenaissance')->getData());
                $delegue->setLieunaissance($form->get('lieunaissance')->getData());
                $delegue->setTelephone($form->get('telephone')->getData());
                $delegue->setFonction($form->get('fonction')->getData());
                $roles = ['ROLE_HABITANT', 'ROLE_DELEGUE'];
                $delegue->setRoles($roles);
                $delegue->setDeleguenommeur($this->getUser());

                //le delgue doit etre donc un habitant de ce quartier
                $deleguedemandenontraiter = $demandeinscriptionRepository->findOneBy(['Habitant' => $delegue, 'etatdemande' => 'nonTraiter', 'quartier' => $veirifier]);
                $deleguedemandeaccepter = $demandeinscriptionRepository->findOneBy(['Habitant' => $delegue, 'etatdemande' => 'accepter', 'quartier' => $veirifier]);
                //donc le personne n'est pas un habitant de ce quartier
                if ($deleguedemandeaccepter == null && $deleguedemandenontraiter == null) {
                    $demandeinscription = new Demandeinscription();
                    $demandeinscription->setDatedemande(new DateTimeImmutable());
                    $demandeinscription->setDatetraitement(new DateTimeImmutable());
                    $demandeinscription->setHabitant($delegue);
                    $demandeinscription->setDelegue($delegue);
                    $demandeinscription->setQuartier($veirifier);
                    $demandeinscription->setEtatdemande('accepter');
                    $entityManager->persist($demandeinscription);
                } else {
                    if ($deleguedemandenontraiter != null) {
                        $deleguedemandenontraiter->setEtatdemande('accepter');
                        $deleguedemandenontraiter->setDatetraitement(new DateTimeImmutable());
                        $deleguedemandenontraiter->setDelegue($delegue);
                        $entityManager->persist($deleguedemandenontraiter);
                    }
                }

                $entityManager->persist($delegue);
                $ancienDelgue->setDeleguenommeur(null);
                $ancienDelgue->setRoles(['ROLE_HABITANT']);
                $entityManager->persist($ancienDelgue);
                $quartierRepository->mettreFinDegue($id->getId());
                $quartierRepository->ajouterUnDelegue($veirifier->getId(), $delegue->getId());
                //envoi du message
                //envoi du message
                $url = $this->generateUrl('app_login', [], urlGeneratorInterface::ABSOLUTE_URL);
                $entityManager->flush();
                $departement = $commune->getDepartement();
                $region = $departement->getRegion();
                $message = "<h1> Salut " . $delegue->getPrenom() . " " . $delegue->getNom() . "</h1>" .
                    '<p>le maire ' . $this->getUser()->getPrenom() . ' ' . $this->getUser()->getNom() .
                    ' du commune de ' . $commune->getNom() . ', département de ' . $departement->getNom() . ', région de ' .
                    $region->getNom() . ' vous a nommé comme délégué au quartier ' . $id->getNom() . '</p>' .
                    '<p>Le compte pour gérer ce quartier est le même que vous avez créer avec cette adresse mail ' . $delegue->getEmail() .
                    '<p>accéder à votre compte en cliquant sur ce lien <a href="' . $url . '">cliquer moi</a></p> ';
                // dd($delegue);
            } else {
                $ancienDelgue->setDeleguenommeur(null);
                $ancienDelgue->setRoles(['ROLE_HABITANT']);
                $entityManager->persist($ancienDelgue);
                $quartierRepository->mettreFinDegue($id->getId());
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
                $roles = ['ROLE_HABITANT', 'ROLE_DELEGUE'];
                $user->setRoles($roles);
                $user->setDeleguenommeur($this->getUser());
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
                $quartierRepository->ajouterUnDelegue($veirifier->getId(), $user->getId());
                $entityManager->flush();
                //envoid du message
                $url = $this->generateUrl('app_login', [], urlGeneratorInterface::ABSOLUTE_URL);
                $entityManager->flush();
                $departement = $commune->getDepartement();
                $region = $departement->getRegion();
                $message = "<h1> Salut " . $user->getPrenom() . " " . $user->getNom() . "</h1>" .
                    '<p>le maire ' . $this->getUser()->getPrenom() . ' ' . $this->getUser()->getNom() .
                    ' du commune de ' . $commune->getNom() . ', département de ' . $departement->getNom() . ', région de ' .
                    $region->getNom() . ' vous a nommé comme délégué au quartier ' . $id->getNom() . '</p>' .
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
            $message = "<h1> Salut " . $ancienDelgue->getPrenom() . " " . $ancienDelgue->getNom() . "</h1>" .
                '<p>le maire ' . $this->getUser()->getPrenom() . ' ' . $this->getUser()->getNom() .
                ' du commune de ' . $commune->getNom() . ', département de ' . $departement->getNom() . ', région de ' .
                $region->getNom() . ' vient de vous remplacer par un autre délégué au quartier ' . $id->getNom() . '</p>' .
                '<p>désormais vous n\'êtes plus un délégué de ce quartier mais plutôt un habitant.</p>
                  <p>le maire vous remercie de votre travail impeccable</p>
                  
                  <p>accéder à votre compte,
                    en cliquant sur ce lien <a href="' . $url . '">cliquer moi</a></p> ';
            $email = (new Email())
                ->from('sama.certificat@gmail.com')
                ->to($ancienDelgue->getEmail())
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

        return $this->render('maire/remplacerdelegue.html.twig', [
            'registrationForm' => $form->createView(), 'idquartier' => $veirifier->getId()
        ]);
    }
   
    //verification de la validité d'un certificat de domicile
    #[Route('/maire/verification/certificat/{idQrcode}', name: 'app_maire_verification_certificat', requirements: ['idQrcode' => '.+'])]
    public function verificationDeCertificat(PersonneRepository $personneRepository, $idQrcode, EntityManagerInterface $entityManager, DemandecretificatRepository $demandecretificatRepository)
    {
        //cherchons l'id du comune ou le maire dirige
        $tableau = $personneRepository->jointuremairecommune($this->getUser()->getId());
        $idcommune = $tableau[0]['commune_id'];
        $demandeCertificat  = $demandecretificatRepository->findOneBy(['idQrcode' => $idQrcode]);
        //cherchons si le certificat est pour le maire c a d son commune

        if ($demandeCertificat == null) {
            $this->addFlash('info', ' Attention Ce certificat a été falsifié donc c\'est un document illicite .
            Veuillez contacter les administrateurs au numéro 774693621 ou au 
             numero 781341751');
            return $this->redirectToRoute('app_principal');
        }
        if ($demandeCertificat->getQuartier()->getCommune()->getId() != $idcommune) {
            $this->addFlash('info', ' Attention Ce certificat n\'est pas de votre commune .Il se peut que  le demandeur 
            vous a présenté un document falsifié  .Veuillez contacter les administrateurs au numéro 774693621 ou au 
             numero 781341751');
            return $this->redirectToRoute('app_principal');
        }
        if ($demandeCertificat->getEtatdemande() == 'valide') {
            $this->addFlash('success', ' Ce certificat est bel et bien valide et a été créé par un de vos délégués actuels ou anciens délégués de votre commune');
            $demandeCertificat->setEtatdemande('invalide');
            $entityManager->persist($demandeCertificat);
            $entityManager->flush();
        } else {
            $this->addFlash('success', ' Attention Ce certificat n\'est plus  valide');
        }


        return $this->render('maire/veifiercertificat.html.twig', ['certificat' => $demandeCertificat]);
    }
}
