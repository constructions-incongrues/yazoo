<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Link>
 *
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkRepository extends ServiceEntityRepository
{
    private $verbose=false;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    public function verbose(bool $b):bool
    {
        $this->verbose=$b;
        return $this->verbose;
    }

    public function save(Link $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Save a list of urls at once
     *
     * @param array $urls
     * @return int
     */
    public function saveUrls(array $urls, int $comment_id, int $discussion_id, int $contributor_id)
    {
        $count=0;
        foreach ($urls as $url) {
            if(!$url)continue;
            $this->saveUrl($url, $comment_id, $discussion_id, $contributor_id);
            $count++;
        }
        return $count;
    }

    /**
     * Save one URL record
     *
     * @param string $url
     * @param integer $comment_id
     * @param integer $discussion_id
     * @param integer $contributor_id
     * @return void
     */
    public function saveUrl(string $url, int $comment_id, int $discussion_id, int $contributor_id)
    {

        // No http ?
        if (preg_match("/^www/", $url)) {
            $url='http://'.$url;
        }
        if (preg_match("/^http:[\/]{1}\w/", $url)) {
            $url=str_replace('http:/','http://',$url);
        }


        // AutoFix https for known domains
        $url=$this->httpsFix($url);

        $link=new Link();
        $link->setContributorId($contributor_id);
        $link->setCommentId($comment_id);
        $link->setDiscussionId($discussion_id);
        $link->setUrl(trim($url));

        //temporary name
        $link->setTitle(basename($url));


        // Fast detect provider
        $provider=$this->url2provider($url);
        if (is_string($provider)) {
            $link->setProvider($provider);
        }
        $this->save($link, true);
    }


    /**
     * Fast provider resolution using the url. (fast detection during Sync)
     * Must move to extract services
     *
     * @param string $url
     * @return void
     */
    private function url2provider(string $url):string|null
    {
        $x=parse_url($url);
        if(!$x||!$x['host'])return null;
        //print_r($x);
        $x['host']=str_replace('www.','',$x['host']);
        switch($x['host']){//alphabetical prder

            case 'bandcamp.com':
                return 'Bandcamp';

            case 'facebook.com':
                return 'Facebook';

            case 'free.fr':
                return 'Free';

            case 'imgur.com':
            case 'i.imgur.com':
                return 'Imgur';

            case 'last.fm':
            case 'lastfm.fr':
                return 'Lastfm';

            case 'mixcloud.com':
                return 'Mixcloud';

            case 'ouiedire.net':
                return 'Ouiedire';

                    case 'soundcloud.com':
                return 'Soundcloud';

            case 'vimeo.com':
                return 'Vimeo';

            case 'youtube.com':
            case 'youtu.be':
                return 'Youtube';
        }

        if(preg_match("/\bbandcamp\.com/",$x['host']))return 'Bandcamp';
        if(preg_match("/\bblogspot\.com/",$x['host']))return 'Blogspot';
        if(preg_match("/\bfree\.fr/",$x['host']))return 'Free';
        if(preg_match("/\bmyspace\.com/",$x['host']))return 'Myspace';
        if(preg_match("/\bphotobucket\.com/",$x['host']))return 'Photobucket';
        if(preg_match("/\btumblr\.com/",$x['host']))return 'Tumblr';

        return null;
    }


    /**
     * Replace http with https for known domains
     * May help to get httpstatus 200 instead of 301
     *
     * @param string $url
     * @return string
     */
    private function httpsFix(string $url):string
    {
        //TODO: move this method somewhere else.
        $list=[];
        // We know those providers use https
        $list[]='www.youtube.com';
        $list[]='www.myspace.com';
        $list[]='www.dailymotion.com';
        $list[]='i.imgur.com';
        //$list[]='any.bandcamp.com';//not sure how to do it ?

        $x=parse_url($url);

        if (!$x) {
            return $url;
        }

        if ($x['scheme']=='https') {//ok
            return $url;
        }

        //echo "$url\n";
        //print_r($x);

        if ($x['host']&&in_array($x['host'],$list)) {
            //exit("$url http->https");
            return str_replace('http://','https://',$url);
        }
        return $url;
    }

    public function delete(Link $entity): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($entity);
        $entityManager->flush();
    }


   /**
    * @return Link[] Returns an array of Link objects
    */

    public function findByProvider(string $value, int $limit=30): array
    {
        return $this->createQueryBuilder('l')
            //->where('l.status<1')
            ->andWhere('l.status IS NULL')
            ->andWhere('l.provider = :val')
            ->setParameter('val', $value)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Return provider links, ordered by visited_at
     * This is used by crawlers, so we focus on the oldest updated records.
     * @param string $provider
     * @return void
     */
    public function findWaitingProvider(string $provider, int $limit=30): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.provider = :provider')
            ->setParameter('provider', $provider)
            ->orderBy('l.visited_at', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findWaitingImages(int $limit=30): array
    {
        return $this->createQueryBuilder('l')
        ->andWhere('l.mimetype LIKE :mimetype')
        ->setParameter('mimetype', 'image%')
        ->orderBy('l.visited_at', 'ASC')//get the `oldest` records
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }


   public function findWhereStatusIsNull(): array
   {
       return $this->createQueryBuilder('l')
           ->andWhere('l.status IS NULL')
           //->setParameter('val', $value)
           ->orderBy('l.id', 'DESC')
           ->setMaxResults(30)
           ->getQuery()
           ->getResult();
   }


   public function findVideos(): array
   {
        return $this->createQueryBuilder('l')
           ->andWhere('l.status > 0')
           ->andWhere('l.provider LIKE :provider')
           ->setParameter('provider', 'youtube')
           //->orderBy('l.id', 'DESC')
           ->setMaxResults(30)
           ->getQuery()
           ->getResult();
   }

   public function findImages():array
   {
        return $this->createQueryBuilder('l')
           ->andWhere('l.status > 0')
           ->andWhere('l.mimetype LIKE :mimetype')
           ->setParameter('mimetype', 'image%')
           //->orderBy('l.id', 'DESC')
           //->setMaxResults(30)
           ->getQuery()
           ->getResult();
   }

   /*
   //this is crap
   public function findExtension(string $ext):array
   {
    return $this->createQueryBuilder('l')
           ->andWhere('l.status < 1')
           ->andWhere('l.url LIKE :ext')
           ->setParameter('ext', '%'.$ext)
           //->orderBy('l.id', 'DESC')
           ->setMaxResults(30)
           ->getQuery()
           ->getResult();
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
