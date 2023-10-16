<?php

namespace App\Repository;

use App\Entity\Metadata;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Metadata>
 *
 * @method Metadata|null find($id, $lockMode = null, $lockVersion = null)
 * @method Metadata|null findOneBy(array $criteria, array $orderBy = null)
 * @method Metadata[]    findAll()
 * @method Metadata[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetadataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Metadata::class);
    }

//    /**
//     * @return Metadata[] Returns an array of Metadata objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Metadata
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
