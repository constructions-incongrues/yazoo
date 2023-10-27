<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function parseQuery(string $q):array
    {
        $dat=[];
        $dat['title']=null;
        $dat['status']=null;
        $dat['provider']=null;
        $dat['contributor']=null;
        $dat['discussion']=null;
        $dat['mimetype']=null;
        $dat['orderby']=null;
        $dat['q']=$q;

        if(preg_match("/\btitle:([a-z]+)/i",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $dat['title']=$o[1];
        }

        if(preg_match("/\bstatus:([0-9]{1,3})/",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $dat['status']=$o[1];
        }

        if(preg_match("/\bprovider:([a-z]+)/i",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $dat['provider']=$o[1];
        }

        if(preg_match("/\b(contributor|user):([0-9]+)/i",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $dat['contributor']=$o[1];
        }

        if(preg_match("/\bdiscussion:([0-9]+)/i",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $dat['discussion']=$o[1];
        }

        if(preg_match("/\bmimetype:([a-z\/]+)/i",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $dat['mimetype']=$o[1];
        }

        if(preg_match("/\borderby:([a-z\/]+)/i",$q,$o)){
            $q=str_replace($o[0],"",$q);
            $dat['orderby']=$o[1];
        }


        $dat['q']=$q;//leftover

        return $dat;
    }


    public function applyFilters($queryBuilder, $Q):QueryBuilder
    {
        //Apply Filters
        if ($Q['title']!==null) {
            $queryBuilder->andWhere("l.title LIKE :title")
            ->setParameter('title', '%'.$Q['title'].'%');
        }

        if ($Q['status']!==null) {
            $queryBuilder->andWhere("l.status= :status")
            ->setParameter('status', $Q['status']);
        //}else{//this one mess with the crawler
        //    $queryBuilder->andWhere('l.status>=200 AND l.status<400');
        }

        if ($Q['provider']>0) {
            $queryBuilder->andWhere("l.provider LIKE :provider")
            ->setParameter('provider', $Q['provider']);
        }

        if ($Q['discussion']>0) {
            $queryBuilder->andWhere("l.discussion_id LIKE :discussion_id")
            ->setParameter('discussion_id', $Q['discussion']);
        }

        if ($Q['contributor']>0) {
            $queryBuilder->andWhere("l.contributor_id LIKE :contributor")
            ->setParameter('contributor', $Q['contributor']);
        }

        if ($Q['mimetype']>0) {
            $queryBuilder->andWhere("l.mimetype LIKE :mimetype")
            ->setParameter('mimetype', '%'.$Q['mimetype'].'%');
        }

        if ($Q['orderby']) {
            $queryBuilder->addOrderBy('l.visited_at','ASC');//
        }else{
            $queryBuilder->addOrderBy('l.visited_at','DESC');//
        }

        return $queryBuilder;
    }

    /**
     * Search link records
     *
     * @param string $q
     * @return void
     */
    public function search(string $q, int $page=1, int $limit=10): array
    {

        if ($page<1) {//fix input mistakes
            $page=1;
        }

        $Q=$this->parseQuery($q);//extract filters (status:200, provider:youtube)

        $queryBuilder = $this->createQueryBuilder('l')
           //->select('l.id, l.url, l.provider, l.comment_id, l.title, l.description, l.status, l.mimetype')
           ->where('(l.url LIKE :searchTerm OR l.title LIKE :searchTerm)')
           ->setParameter('searchTerm', '%'.trim($Q['q']).'%')

           ->setMaxResults($limit)
           ->setFirstResult(($page - 1) * $limit);

        //Apply Filters
        $queryBuilder =$this->applyFilters($queryBuilder, $Q);

        /*
        if ($Q['title']!==null) {
            $queryBuilder->andWhere("l.title LIKE :title")
            ->setParameter('title', '%'.$Q['title'].'%');
        }

        if ($Q['status']!==null) {
            $queryBuilder->andWhere("l.status= :status")
            ->setParameter('status', $Q['status']);
        }else{
            $queryBuilder->andWhere('l.status>=200 AND l.status<400');
        }

        if ($Q['provider']>0) {
            $queryBuilder->andWhere("l.provider LIKE :provider")
            ->setParameter('provider', $Q['provider']);
        }

        if ($Q['discussion']>0) {
            $queryBuilder->andWhere("l.discussion_id LIKE :discussion_id")
            ->setParameter('discussion_id', $Q['discussion']);
        }

        if ($Q['contributor']>0) {
            $queryBuilder->andWhere("l.contributor_id LIKE :contributor")
            ->setParameter('contributor', $Q['contributor']);
        }

        if ($Q['mimetype']>0) {
            $queryBuilder->andWhere("l.mimetype LIKE :mimetype")
            ->setParameter('mimetype', '%'.$Q['mimetype'].'%');
        }
        */

        // Create a custom DQL function for RAND()
        //$queryBuilder->addOrderBy('l.id','DESC');


        // Clone the original query builder to create a count query builder
        $countQueryBuilder = clone $queryBuilder;
        $countQueryBuilder->select('COUNT(l.id)');
        $count = (int)$countQueryBuilder->getQuery()->getSingleScalarResult();

        // Compute page number
        $pages=1;
        if ($count>0 && $limit>0) {
            $pages=(int)ceil($count/$limit);
        }

        $query=$queryBuilder->getQuery();
        $results = $query->getResult();

        return [
            'q' => $q,
            'count' => $count,
            'limit' => $limit,
            'pages' => $pages,
            'page_index' => $page,
            'results' => $results,
        ];
    }



    /**
     * Image search.
     * Only return links with a IMAGE mimetype. (the crawler must have done its job)
     * @param string $q
     * @param integer $page
     * @param integer $limit
     * @return void
     */
    public function searchImages(string $q, int $page=1, int $limit=10):array
    {
        $Q=$this->parseQuery($q);//extract filters (status:200, provider:youtube)

        $queryBuilder = $this->createQueryBuilder('l')
            ->where('(l.url LIKE :searchTerm OR l.title LIKE :searchTerm)')
            ->setParameter('searchTerm', '%'.trim((string)$Q['q']).'%')
            ->andWhere("l.mimetype LIKE :mimetype")
            ->setParameter('mimetype', 'image/%')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit);


        $queryBuilder =$this->applyFilters($queryBuilder, $Q);

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


    public function searchVideos(string $q, int $page=1, int $limit=10):array
    {
        $Q=$this->parseQuery($q);//extract filters (status:200, provider:youtube)

        $queryBuilder = $this->createQueryBuilder('l')
            ->where('(l.url LIKE :searchTerm OR l.title LIKE :searchTerm)')

            ->setParameter('searchTerm', '%'.trim((string)$Q['q']).'%')

            ->andWhere("l.status < 400")
            ->andWhere("l.provider LIKE :provider")
            ->setParameter('provider', 'youtube')//99percent of video content

            ->orderBy('l.visited_at', 'DESC')

            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit);


        $queryBuilder =$this->applyFilters($queryBuilder, $Q);

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

}
