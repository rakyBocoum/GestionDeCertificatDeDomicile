<?php

namespace App\Repository;

use App\Entity\Demandeinscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Demandeinscription>
 *
 * @method Demandeinscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demandeinscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demandeinscription[]    findAll()
 * @method Demandeinscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeinscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demandeinscription::class);
    }

    public function save(Demandeinscription $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Demandeinscription $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    //l'ensemble des demandes d'inscription accepter dans un commune (ensemble habitant d'un commune)
    public function enembleHabitantCommune($idcommune){
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
        select * from demandeinscription where etatdemande=? and quartier_id in(select quartier.id from quartier where commune_id=?)
           ';
        $stmt = $conn->prepare($sql);
        $resultSet =$stmt->executeQuery(['accepter',$idcommune]);
        return $resultSet->fetchAllAssociative();
    }
    //rechercher une personne dans une commune a partir de son nom ou prenom
    public function rechercherUnePersonneCommune($idcommune,$info){
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
        select distinct demandeinscription.* from demandeinscription where etatdemande=? and quartier_id in(select quartier.id from quartier where commune_id=?
        ) and habitant_id in (select id from personne where concat(nom," ",prenom) like  "%'.$info.'%" or concat(prenom," ",nom) like "%'.$info.'%" or nom 
         like "%'.$info.'%" or prenom like "%'.$info.'%"
           )';
        $stmt = $conn->prepare($sql);
        $resultSet =$stmt->executeQuery(['accepter',$idcommune]);
        return $resultSet->fetchAllAssociative();
    }
     //rechercher une personne dans un quartier a partir de son nom ou prenom
     public function rechercherUnePersonneQuartier($idquartier,$info){
        $conn = $this->getEntityManager()->getConnection();
        

        $sql = '
        select distinct demandeinscription.* from demandeinscription where etatdemande=? and quartier_id=? 
         and habitant_id in (select id from personne where concat(nom," ",prenom) like  "%'.$info.'%" or concat(prenom," ",nom) like "%'.$info.'%" or nom 
         like "%'.$info.'%" or prenom like "%'.$info.'%"
           )';
        $stmt = $conn->prepare($sql);
        $resultSet =$stmt->executeQuery(['accepter',$idquartier]);
        return $resultSet->fetchAllAssociative();
    }
    

//    /**
//     * @return Demandeinscription[] Returns an array of Demandeinscription objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Demandeinscription
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
