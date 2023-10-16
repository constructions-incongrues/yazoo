<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
#use Doctrine\ORM\Query\Expr;

/**
 * @extends ServiceEntityRepository<Link>
 *
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }



   /**
    * @return Link[] Returns an array of Link2 objects
    */

    public function findByStatusField($value): array
   {
       return $this->createQueryBuilder('l')
           ->andWhere('l.status = :val')
           ->setParameter('val', $value)
           ->orderBy('l.id', 'ASC')
           ->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
   }


   public function findWhereStatusIsNull(): array
   {
       return $this->createQueryBuilder('l')
           ->andWhere('l.status IS NULL')
           //->setParameter('val', $value)
           //->orderBy('l.id', 'ASC')
           ->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
   }



    /**
     * Search link records
     *
     * @param string $q
     * @return void
     */
    public function search(string $q, int $page=1, int $limit=10): array
    {

        //extract filters (status:200, provider:youtube)
        $status=null;
        $provider=null;
        $contributor=null;
        $discussion=null;

        if(preg_match("/\bstatus:([0-9]{1,3})/",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $status=$o[1];
        }

        if(preg_match("/\bprovider:([a-z]+)/i",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $provider=$o[1];
        }

        if(preg_match("/\b(contributor|user):([0-9]+)/i",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $contributor=$o[1];
        }

        if(preg_match("/\bdiscussion:([0-9]+)/i",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $discussion=$o[1];
        }

        $queryBuilder = $this->createQueryBuilder('l')
           ->select('l.id, l.url, l.comment_id, l.title, l.description, l.status, l.mimetype')
           ->where('(l.url LIKE :searchTerm OR l.title LIKE :searchTerm)')
           ->setParameter('searchTerm', '%'.trim($q).'%')

           ->setMaxResults($limit)
           ->setFirstResult(($page - 1) * $limit);

        // Create a custom DQL function for RAND()
        $queryBuilder->addOrderBy('l.id','DESC');


        if ($status>0) {
            $queryBuilder->andWhere("l.status= :status")
            ->setParameter('status', $status);
        }else{
            $queryBuilder->andWhere('l.status>=200 AND l.status<400');
        }

        if ($provider>0) {
            $queryBuilder->andWhere("l.provider LIKE :provider")
            ->setParameter('provider', $provider);
        }

        if ($discussion>0) {
            $queryBuilder->andWhere("l.discussion_id LIKE :discussion_id")
            ->setParameter('discussion_id', $discussion);
        }

        if ($contributor>0) {
            $queryBuilder->andWhere("l.contributor_id LIKE :contributor")
            ->setParameter('contributor', $contributor);
        }


        // Clone the original query builder to create a count query builder
        $countQueryBuilder = clone $queryBuilder;
        $countQueryBuilder->select('COUNT(l.id)');
        $count = (int)$countQueryBuilder->getQuery()->getSingleScalarResult();

        $query=$queryBuilder->getQuery();

        //$sql = $query->getSQL();

        $results = $query->getResult();

        return [
            'count' => $count,
            'results' => $results,
        ];
    }


    /*
    public function searchImages(string $q, int $page=1, int $limit=10)
    {
        return [
            'count' => $count,
            'results' => $results,
        ];
    }
    */
}
