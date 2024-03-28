<?php

namespace App\Twig\Runtime;

use App\Entity\Personne;
use App\Entity\Quartier;
use App\Repository\CommuneRepository;
use App\Repository\DemandecretificatRepository;
use App\Repository\DemandeinscriptionRepository;
use App\Repository\PersonneRepository;
use App\Repository\QuartierRepository;
use Twig\Extension\RuntimeExtensionInterface;
use DateTime;

class FonctionPersonnaliserRuntime implements RuntimeExtensionInterface
{
    public function __construct(private CommuneRepository $communeRepository, private QuartierRepository $quartierRepository, private PersonneRepository $personneRepository, private DemandeinscriptionRepository $demandeinscriptionRepository,private DemandecretificatRepository $demandecretificatRepository)
    {
        // Inject dependencies if needed
    }

    public function etatDemande($value, $personne)
    {
        if ($value == 'nonTraiter') {
            return 'non traité';
        } elseif ($value == 'refuser')
            return "Refusé";
        elseif ($value == 'accepter') {
            return "Accepté";
        }
        elseif($value=='annuler')
            return "Accepté puis annuler par vous";
            else
            return 'accepté puis retiré par un délégué';
    }
    //pour touver l'etat de demande d'un certificat
    public function etatDemandeCertificat($demande){
        if($demande->getEtatdemande()=='valide')
        return true;
        else
        return false;
    }
    //obtenir le nom du fichier facture ou photo recent
    public function getNomFichier($type, $id)
    {
        $jpg = $id . '.jpg';
        $png = $id . '.png';
        $jpeg = $id . '.jpeg';

        if ($type == 'facture') {
            if (file_exists('facture/' . $jpg))
                return 'facture/' . $jpg;
            elseif (file_exists('facture/' . $png))
                return 'facture/' . $png;
            elseif (file_exists('facture/' . $jpeg))
                return 'facture/' . $jpeg;
            else
                return null;
        } else {

            if (file_exists('imagerecent/' . $jpg))
                return 'imagerecent/' . $jpg;
            elseif (file_exists('imagerecent/' . $png))
                return 'imagerecent/' . $png;
            elseif (file_exists('imagerecent/' . $jpeg))
                return 'imagerecent/' . $jpeg;
            else
                return null;
        }
    }
    //obtenir le delgue d'un quartier en cours
    public function obtenirDelegueEncours($idquartier)
    {
        $tableau = $this->quartierRepository->jointuredelguequartier($idquartier);
       


        if ($tableau != null) {
            $personne = $this->personneRepository->find($tableau[0]['id']);
            return $personne;
        }

        return null;
    }
    //obtenir le maire actuel d'un quartier en cours au niveau d'un commune
    public function obtenirMaireEncours($idcommune)
    {
        $tableau = $this->communeRepository->jointureMaireCommune($idcommune);
       


        if ($tableau != null) {
            $personne = $this->personneRepository->find($tableau[0]['id']);
            return $personne;
        }

        return null;
    }

    //obtenir le date de debut du delegue encours
    public function obtenirDateDeDebutDelegue($idquartier)
    {
        $tableau = $this->quartierRepository->jointuredelguequartier($idquartier);
        
        //dd($tableau[0]['datededebut']);
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $tableau[0]['datededebut']);
          
       $date= $date->format('m/d/Y H:i a');
      

        return $date;
    }
    //verifier si une personne est lui meme le dlegue grace a app.user
    public function isRoleDelgue($personne,$delegue)
    {
        
        if($personne->getId()==$delegue->getId()){
             return true;
        }else{
            return false;
        }
    }
    //le maire liste les habitant donc il utilise cette fontion au niveau du twig
     //verifier si une personne est lui meme le dlegue grace a app.user
     public function maireListeHabitant($demande,$infoVoulue)
     {

         
         $personneId=$demande['habitant_id'];
         $personne=$this->personneRepository->find($personneId);
         $quartier=$this->quartierRepository->find($demande['quartier_id']);
         if($infoVoulue=='nom')
         return $personne->getNom();
         if($infoVoulue=='prenom')
        return $personne->getPrenom();
        if($infoVoulue=='ddn')
        return $personne->getDatenaissance();
        if($infoVoulue=='ldn')
        return $personne->getLieunaissance();
        if($infoVoulue=='fonction')
        return $personne->getFonction();
        if($infoVoulue=='quartier'){
            return $quartier->getNom();
        }
        if($infoVoulue=='entite'){
            return $personne;
        }
        if($infoVoulue=='role'){
            $delegueEncours=$this->obtenirDelegueEncours($quartier->getId());
            if($this->isRoleDelgue($personne,$delegueEncours)){
                return 'Délégué';
            }else
            return 'Habitant';
        }
     }
    //obtenir le nombre habitant(inscrits valide) d'un quartier 
    public function nombreHabitant($idquartier)
    {
        $quartier = $this->quartierRepository->find($idquartier);
        $nombre = $this->demandeinscriptionRepository->findBy(['quartier' => $quartier, 'etatdemande' => 'accepter']);
        return count($nombre);


        //  if($tableau!=null)
        //  {
        //    $personne=$this->personneRepository->find($tableau[0]['id']);
        //    return $personne;
        //  }

        //  return null;

    }
    //le nombre total de certificat dans un quartier
    public function nombreCertificatQuartier($idquartier)
    {
        $quartier = $this->quartierRepository->find($idquartier);
        $nombre = $this->demandecretificatRepository->findBy(['quartier' => $quartier]);
        return count($nombre);



    }
      //le nombre de demande inscription accepter ou refuser par le delegue dans un quartier
      public function nombreDemandeInscriptionDeleue($idquartier,$type)
      {
        $quartier = $this->quartierRepository->find($idquartier);
        $nombre=0;
        $delegueEncours=$this->obtenirDelegueEncours($idquartier);
        if($delegueEncours==null)
        return 'pas de delegue';
        $nombre = $this->demandeinscriptionRepository->findBy(['delegue'=>$delegueEncours,'quartier' => $quartier, 'etatdemande' => $type]);
        return count($nombre);
          
  
  
  
      }
        //le nombre de demande inscription nom traiter dans un quartier
        public function nombreDemandeInscriptionNonTraiter($delegue)
        {
            //recuperons le quartier ou le delegue dirige
            $tableau = $this->personneRepository->jointuredelguequartier($delegue->getId());
            $idquartier = $tableau[0]['quartier_id'];
            $quartier = $this->quartierRepository->find($idquartier);
          $nombre = $this->demandeinscriptionRepository->findBy(['quartier' => $quartier, 'etatdemande' =>'nontraiter']);
          return count($nombre);
            
    
    
    
        }
}
