<?php

namespace App\Repository;

use App\Entity\Discussion;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Discussion>
 *
 * @method Discussion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Discussion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Discussion[]    findAll()
 * @method Discussion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscussionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Discussion::class);
    }

    public function save(Discussion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByHighestId()
    {
        return $this->createQueryBuilder('d')
           ->orderBy('d.id', 'DESC')
           ->setMaxResults(1)
           ->getQuery()
           ->getOneOrNullResult();
    }

//    /**
//     * @return Discussion[] Returns an array of Discussion objects
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

   public function findOneById($value): ?Discussion
   {
        return $this->createQueryBuilder('d')
           ->andWhere('d.discussion_id = :id')
           ->setParameter('id', $value)
           ->getQuery()
           ->getOneOrNullResult();
   }


   /**
    * Get Discussion name
    *
    * @param integer $discussion_id
    * @return string
    */
   public function getName(int $discussion_id):string
   {
        $discussion=$this->findOneById($discussion_id);
        if ($discussion) {
            return $discussion->getName();
        }
        return '';
   }

   public function saveDiscussion(int $discussion_id, string $name, string $dateCreated): Discussion
   {
        $discussion=new Discussion();
        $discussion->setDiscussionId($discussion_id);
        $discussion->setName($name);
        $discussion->setCreatedAt(DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $dateCreated));
        $this->save($discussion, true);
        return $discussion;
   }


}
