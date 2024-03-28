<?php

namespace App\Repository;

use App\Entity\Commune;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commune>
 *
 * @method Commune|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commune|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commune[]    findAll()
 * @method Commune[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommuneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commune::class);
    }

    public function save(Commune $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Commune $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
     //pour trouver le maire en cours au niveau d'une commune
     public function jointureMaireCommune(int $idcommune)
     {
         $conn = $this->getEntityManager()->getConnection();
 
         $sql = '
             SELECT personne.id,personne.nom,personne.prenom,personne_commune.datededebut FROM personne_commune,commune,personne WHERE personne_commune.commune_id= commune.id
              and personne_commune.personne_id=personne.id and commune_id=? and etat=? ';
         $stmt = $conn->prepare($sql);
         $etat='enCours';
         $resultSet = $stmt->executeQuery([$idcommune,$etat]);
         
         
 
         // returns an array of arrays (i.e. a raw data set)
         return $resultSet->fetchAllAssociative();
     }

     //trouver les communes sans maire dans une commune
    public function communeSansMaire(int $idDepartement)
    {
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
            SELECT distinct * FROM commune WHERE departement_id=?
             and commune.id Not in(select personne_commune.commune_id from personne_commune where etat=? order by nom)';
        $stmt = $conn->prepare($sql);
        $etat='enCours';
        $resultSet = $stmt->executeQuery([$idDepartement,$etat]);
        // returns an array of arrays (i.e. a raw data set)
        
        return $resultSet->fetchAllAssociative();
    }
     //trouver les commune qui ont un maire dans un departement
     public function quartierAvecMaire(int $idDepartement)
     {
         $conn = $this->getEntityManager()->getConnection();
         
 
         $sql = '
             SELECT distinct * FROM commune WHERE departement_id=?
              and commune.id  In (select personne_commune.commune_id from personne_commune where etat=? order by nom )';
         $stmt = $conn->prepare($sql);
         $etat='enCours';
         $resultSet = $stmt->executeQuery([$idDepartement,$etat]);
         // returns an array of arrays (i.e. a raw data set)
         
         return $resultSet->fetchAllAssociative();
     }
    //verifier si une commune a un maire
    public function uneCommuneSansMaire(int $idcommune)
    {
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
            SELECT * FROM personne_commune WHERE personne_commune.commune_id=?
             and etat=? ';
        $stmt = $conn->prepare($sql);
        $etat='enCours';
        $resultSet = $stmt->executeQuery([$idcommune,$etat]);
        // returns an array of arrays (i.e. a raw data set)
        
        return $resultSet->fetchAllAssociative();
    }
    //ajouter un maire
    public function ajouterUnMaire($idCommune,$idMaire){
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
            insert into personne_commune(commune_id,personne_id,etat,datededebut)values(?,?,?,?)';
        $stmt = $conn->prepare($sql);
        $etat='enCours';
        $tempDate = DateTime::createFromFormat('j-M-Y', '15-Feb-2009');
         $date=$tempDate->format('Y-m-d H:i:s');
        $stmt->executeQuery([$idCommune,$idMaire,'encours',$date]);
    }
     //mettre fin le mandat d'un Maire
     public function mettreFinMaire($idCommune){
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
            update personne_commune set datedefin=?,etat=? where commune_id=? and etat=?';
        $stmt = $conn->prepare($sql);
        $etat='enCours';
        $tempDate = DateTime::createFromFormat('j-M-Y', '15-Feb-2009');
         $date=$tempDate->format('Y-m-d H:i:s');
        $stmt->executeQuery([$date,'fin',$idCommune,'encours']);
    }





//    /**
//     * @return Commune[] Returns an array of Commune objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Commune
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
