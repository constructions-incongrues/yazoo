<?php

namespace App\Repository;

use App\Entity\Link;
use App\Service\HttpStatusService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Link>
 */
class StatRepository extends ServiceEntityRepository
{
    private $httpStatusService;
    public function __construct(ManagerRegistry $registry, HttpStatusService $httpStatusService)
    {
        parent::__construct($registry, Link::class);
        $this->httpStatusService=$httpStatusService;
    }


    /**
     * Return total number of links
     *
     * @return void
     */

    public function countLinks():int
    {
        $max=$this->createQueryBuilder('e')
           ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
        return $max|0;
    }

    //- Count By status code
    //- Count by filetype (extension)
    //- Count By Users
    //- By Provider

    public function countVisitedLinks():int
    {
        $oneMonthAgo = new \DateTimeImmutable();
        $oneMonthAgo = $oneMonthAgo->modify('-1 month');

        $max=$this->createQueryBuilder('e')
           ->select('COUNT(e.id)')
           ->where('e.visited_at IS NOT NULL')
           ->andWhere('e.visited_at > :oneMonthAgo')
            ->setParameter('oneMonthAgo', $oneMonthAgo)
            ->getQuery()
            ->getSingleScalarResult();
        return $max|0;
    }


    public function statusCodes(string $provider='')
    {

        $queryBuilder= $this->createQueryBuilder('l')
            ->select('COUNT(l.id) AS num','l.status')
            ->groupBy('l.status')
            ->orderBy('num', 'DESC');

        if ($provider) {
            $queryBuilder->andWhere('l.provider LIKE :provider')
                ->setParameter('provider', $provider);
        }


        $results= $queryBuilder->getQuery()->getResult();

        foreach($results as $k=>$v){
            if($v['status']===0){
                $results[$k]['info']='Unreachable';
            }elseif(!$v['status']){
                $results[$k]['info']='Waiting';
            }else{
                $results[$k]['info']=$this->httpStatusService->codeName($v['status']|0);
            }
        }

        return $results;
    }






    /**
     * Top Forum-Link Contributors
     *
     * @return void
     */
    public function contributors()
    {
        return $this->createQueryBuilder('l')
           ->select('COUNT(l.id) AS num','l.contributor_id')
           ->groupBy('l.contributor_id')
           ->orderBy('num', 'DESC')
           ->getQuery()
           ->getResult();
    }

    public function discussions()
    {
        $querybuilder=$this->createQueryBuilder('l')
            ->select('COUNT(l.id) AS num', 'l.discussion_id') // Selects the count of records, discussion_id, and discussion_name
            ->where('l.discussion_id > 0') // Exclude link with no forum relation
            //->leftJoin('l.discussion', 'd') // Joins the discussion entity (assuming 'discussion' is the relationship)
            ->groupBy('l.discussion_id') // Groups the results by discussion_id and discussion_name

            ->having('num > 30') // LIMIT to topics of interest
            ->orderBy('num', 'DESC');

        $query=$querybuilder->getQuery();
        $data=$query->getResult();
        return $data;
    }

    public function providers()
    {
        return $this->createQueryBuilder('l')
           ->select('COUNT(l.id) AS num','l.provider')
            ->andWhere("l.provider NOT LIKE '' ")
           ->groupBy('l.provider')
           ->having('num > 4')
           ->orderBy('num', 'DESC')
           ->getQuery()
           ->getResult();
    }

    public function mimetypes(string $provider=''): array
    {
        $queryBuilder=$this->createQueryBuilder('l')
            ->select('COUNT(l.id) AS num','l.mimetype')
            ->andWhere("l.mimetype IS NOT NULL")
            ->groupBy('l.mimetype')
            ->orderBy('num', 'DESC');

        if ($provider) {
            $queryBuilder->andWhere('l.provider LIKE :provider')
                ->setParameter('provider', $provider);
        }

        $results=$queryBuilder->getQuery()
            ->getResult();
        return $results;
    }



    /**
     * Return items with a given httpstatus code
     *
     * @param [type] $value
     * @return array
     */
    /*
    public function findByStatusField($value, int $limit=10): array
   {
       return $this->createQueryBuilder('l')
           ->andWhere('l.status = :val')
           ->setParameter('val', $value)
           ->orderBy('l.id', 'ASC')
           ->setMaxResults($limit)
           ->getQuery()
           ->getResult()
       ;
   }
*/

/*
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
*/




    /**
     * Return the highest Comment_id in the links table, so we know where to search;
     *
     * @return int
     */
    public function findHighestCommentId()
    {
        $max=$this->createQueryBuilder('e')
        ->select('MAX(e.comment_id) as max_value')
        ->getQuery()
        ->getSingleScalarResult();
        return $max|0;
    }

}
