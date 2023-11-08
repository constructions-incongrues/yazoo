<?php

namespace App\Repository;

use App\Entity\Blacklist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
//use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
//use Twig\TokenParser\BlockTokenParser;

/**
 * @extends ServiceEntityRepository<Blacklist>
 *
 * @method Blacklist|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blacklist|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blacklist[]    findAll()
 * @method Blacklist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlacklistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blacklist::class);
    }

    public function save(Blacklist $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Add a blacklist record
     *
     * @param string $host
     * @return Blacklist
     */
    public function add(string $host):Blacklist
    {
        // Make sure host is not in list
        $found=$this->findOneBy(['host'=>$host]);
        if ($found) {
            return $found;
        }

        $blacklist=new Blacklist();
        $blacklist->setHost($host);
        $this->save($blacklist, true);
        return $blacklist;
    }


    public function delete(Blacklist $entity): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($entity);
        $entityManager->flush();
    }


    public function isBlacklisted(string $url)
    {
        //echo "isBlacklisted($url)\n";
        $parse=parse_url($url);
        if(!$parse)return false;
        $host=$parse['host'];
        $found=$this->findOneBy(['host'=>$host]);

        if ($found) {
            return true;
        }

        return false;
    }


//    /**
//     * @return Blacklist[] Returns an array of Blacklist objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Blacklist
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
