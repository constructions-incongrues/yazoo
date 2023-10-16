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



    public function statusCodes()
    {
        $results= $this->createQueryBuilder('l')
            ->select('COUNT(l.id) AS num','l.status')
            ->groupBy('l.status')
            ->orderBy('num', 'DESC')
            ->getQuery()
            ->getResult();

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

    public function providers()
    {
        return $this->createQueryBuilder('l')
           ->select('COUNT(l.id) AS num','l.provider')
            ->andWhere("l.provider NOT LIKE '' ")
           ->groupBy('l.provider')
           ->orderBy('num', 'DESC')
           ->getQuery()
           ->getResult();
    }

    public function mimetypes()
    {
        return $this->createQueryBuilder('l')
           ->select('COUNT(l.id) AS num','l.mimetype')
            ->andWhere("l.mimetype IS NOT NULL")
           ->groupBy('l.mimetype')
           ->orderBy('num', 'DESC')
           ->getQuery()
           ->getResult();
    }



    /**
     * Return items with a given httpstatus code
     *
     * @param [type] $value
     * @return array
     */
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
