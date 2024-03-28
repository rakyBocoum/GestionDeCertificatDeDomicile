<?php

namespace App\Repository;

use App\Entity\Personne;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<Personne>
 *
 * @method Personne|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personne|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personne[]    findAll()
 * @method Personne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonneRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personne::class);
    }

    public function save(Personne $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Personne $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Personne) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }
    //pour trouver l'id d'un delegue dont son mandat est encours dans un quartier.retoune un tableau d'objet
    public function jointuredelguequartier(int $iddelegue)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM personne_quartier WHERE personne_id=? and etat=? ';
        $stmt = $conn->prepare($sql);
        $etat='enCours';
        $resultSet = $stmt->executeQuery([$iddelegue,$etat]);
        
        

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }
     //pour trouver l'id d'un maire dont son mandat est en cours dans un commune
     public function jointuremairecommune(int $idmaire)
     {
         $conn = $this->getEntityManager()->getConnection();
 
         $sql = '
             SELECT * FROM personne_commune WHERE personne_id=? and etat=? ';
         $stmt = $conn->prepare($sql);
         $etat='enCours';
         $resultSet = $stmt->executeQuery([$idmaire,$etat]);

         // returns an array of arrays (i.e. a raw data set)
         return $resultSet->fetchAllAssociative();
     }


//    /**
//     * @return Personne[] Returns an array of Personne objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Personne
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
