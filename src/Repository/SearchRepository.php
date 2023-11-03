<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

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

//    private $paginator;

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

        $dat['include']=[];
        $dat['exclude']=[];

        //Search "group of words" enclosed in double quotes
        if (preg_match("/\"([\w ]+)\"/i", $q, $o)) {
            $q=str_replace($o[0],"",$q);
            $dat['include'][]=$o[1];
        }

        $dat['words']=explode(" ", $q);


        foreach ($dat['words'] as $word) {

            if (preg_match("/^\-/",$word)) {
                $dat['exclude'][]=preg_replace("/^\-/",'',$word);
            } else if(trim($word)) {
                $dat['include'][]=$word;
            }
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
        }else{
            //$queryBuilder->andWhere('l.status>=200 AND l.status<400');//this one mess with the crawler
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

        // Include/Exclude words
        foreach ($Q['include'] as $word) {
            $queryBuilder->andWhere('(l.url LIKE :word OR l.title LIKE :word)')
            ->setParameter('word', '%'.$word.'%');
        }

        return $queryBuilder;
    }

    /**
     * Search link records
     *
     * @param string $q
     * @return void
     */
    public function search(string $q, int $page=1, int $pagesize=10): array
    {

        if ($page<1) {//fix input mistakes
            $page=1;
        }

        $Q=$this->parseQuery($q);//extract filters (status:200, provider:youtube)

        $queryBuilder = $this->createQueryBuilder('l');
        $queryBuilder =$this->applyFilters($queryBuilder, $Q);//Apply Filters
        $queryBuilder->andWhere('l.status>0');//Avoid 'Unavailable or not yet crawled'
        //$queryBuilder->andWhere('l.status<400');//Avoid Link errors

        // Generate the Paginator
		$paginator = new Paginator($queryBuilder->getQuery());
        $paginator->getQuery()
            ->setFirstResult($pagesize * ($page-1)) // set the offset
            ->setMaxResults($pagesize); // set the limit

        $count=count($paginator);

        // Compute page number
        $pages=1;
        if ($count>0 && $pagesize>0) {
            $pages=(int)ceil($count/$pagesize);
        }

        return [
            'q' => $q,
            'debug' => $Q,
            'count' => $count,
            'limit' => $pagesize,
            'pages' => $pages,
            'page_index' => $page,
            'results' => $paginator,
        ];
    }

    public function getResults(int $page)
    {
        //https://nicolasfz-code.medium.com/symfony-paginer-les-r%C3%A9sultats-dune-requ%C3%AAte-avec-doctrine-ebe7873197c9
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
            'q' => $q,
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
            'q' => $q,
            'count' => $count,
            'results' => $results,
        ];
    }

    public function searchTest(string $str,int $page, int $pageSize)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('(l.url LIKE :searchTerm OR l.title LIKE :searchTerm)')
            ->setParameter('searchTerm', '%'.trim((string)$str).'%');

        $query=$queryBuilder->getQuery();

        $paginator  = new Paginator($query);

        // now get one page's items:
        $paginator->getQuery()
            ->setFirstResult($pageSize * ($page-1)) // set the offset
            ->setMaxResults($pageSize); // set the limit

        return $paginator;
        //$totalItems = count($paginator);
        //$pagesCount = ceil($totalItems / $pageSize);
    }

}
