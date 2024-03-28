<?php

namespace App\Repository;

use App\Entity\Quartier;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;

/**
 * @extends ServiceEntityRepository<Quartier>
 *
 * @method Quartier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quartier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quartier[]    findAll()
 * @method Quartier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuartierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quartier::class);
    }

    public function save(Quartier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Quartier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    //pour trouver le delgue en cours au niveau d'un quartier
    public function jointuredelguequartier(int $idquartier)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT personne.id,personne.nom,personne.prenom,personne_quartier.datededebut FROM personne_quartier,quartier,personne WHERE personne_quartier.quartier_id= quartier.id
             and personne_quartier.personne_id=personne.id and quartier_id=? and etat=? ';
        $stmt = $conn->prepare($sql);
        $etat='enCours';
        $resultSet = $stmt->executeQuery([$idquartier,$etat]);
        
        

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }
    //trouver les quartiers sans delegue dans une commune
    public function quartierSansDelegue(int $idcommune)
    {
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
            SELECT distinct * FROM quartier WHERE commune_id=?
             and quartier.id Not in(select personne_quartier.quartier_id from personne_quartier where etat=? order by nom)';
        $stmt = $conn->prepare($sql);
        $etat='enCours';
        $resultSet = $stmt->executeQuery([$idcommune,$etat]);
        // returns an array of arrays (i.e. a raw data set)
        
        return $resultSet->fetchAllAssociative();
    }
     //trouver les quartiers qui ont une delegue dans une commune
     public function quartierAvecDelegue(int $idcommune)
     {
         $conn = $this->getEntityManager()->getConnection();
         
 
         $sql = '
             SELECT distinct * FROM quartier WHERE commune_id=?
              and quartier.id  In (select personne_quartier.quartier_id from personne_quartier where etat=? order by nom )';
         $stmt = $conn->prepare($sql);
         $etat='enCours';
         $resultSet = $stmt->executeQuery([$idcommune,$etat]);
         // returns an array of arrays (i.e. a raw data set)
         
         return $resultSet->fetchAllAssociative();
     }
    //verifier si un quartier a un delegue
    public function unQuartierSansDelegue(int $idquartier)
    {
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
            SELECT * FROM personne_quartier WHERE personne_quartier.quartier_id=?
             and etat=? ';
        $stmt = $conn->prepare($sql);
        $etat='enCours';
        $resultSet = $stmt->executeQuery([$idquartier,$etat]);
        // returns an array of arrays (i.e. a raw data set)
        
        return $resultSet->fetchAllAssociative();
    }
    //ajouter un delegue
    public function ajouterUnDelegue($idquartier,$idelegue){
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
            insert into personne_quartier(quartier_id,personne_id,etat,datededebut)values(?,?,?,?)';
        $stmt = $conn->prepare($sql);
        $etat='enCours';
        $tempDate = DateTime::createFromFormat('j-M-Y', '15-Feb-2009');
         $date=$tempDate->format('Y-m-d H:i:s');
        $stmt->executeQuery([$idquartier,$idelegue,'encours',$date]);
    }
     //mettre fin le mandat d'un delegue
     public function mettreFinDegue($idquartier){
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
            update personne_quartier set datedefin=?,etat=? where quartier_id=? and etat=?';
        $stmt = $conn->prepare($sql);
        $etat='enCours';
        $tempDate = DateTime::createFromFormat('j-M-Y', '15-Feb-2009');
         $date=$tempDate->format('Y-m-d H:i:s');
        $stmt->executeQuery([$date,'fin',$idquartier,'encours']);
    }


//    /**
//     * @return Quartier[] Returns an array of Quartier objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('q.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Quartier
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
