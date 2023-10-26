<?php

namespace App\Controller;

use App\Repository\DiscussionRepository;
use App\Repository\LinkRepository;
use App\Repository\SearchRepository;
use App\Repository\StatRepository;
use App\Service\CommentService;
use App\Service\ExtractService;
use App\Service\MusiqueIncongrueService;
use App\Service\YoutubeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_job')]
    public function index(RouterInterface $routerInterface): Response
    {
        //$routes = $routerInterface->getRouteCollection();
        //dd($routes);

        return $this->render('api/index.html.twig', [
            'controller_name' => 'JobController',
        ]);
    }

    #[Route('/api/sync', methods: ['GET'])]
    public function sync(LinkRepository $linkRepository, DiscussionRepository $discussionRepository, MusiqueIncongrueService $MI, ExtractService $extractService): JsonResponse
    {
        $start_time=time();

        $dat=[];//payload

        //Sync Links
        $dat['new_urls']=0;
        $data=$MI->fetchComments();//Fetch From Directus/MusiqueIncongrues
        foreach($data['data'] as $r){
            $r['urls']=$extractService->extractUrls($r['Body']);
            if (count($r['urls'])) {
                $linkRepository->saveUrls($r['urls'], $r['CommentID'], $r['DiscussionID'], $r['AuthUserID']);
                $dat['new_urls']++;
            }
        }

        //Sync Discussions
        $dat['new_discussions']=0;
        $discussions=$MI->fetchDiscussions();//Fetch From Directus/MusiqueIncongrues
        //$dat['discussions']=$discussions['data'];
        foreach($discussions['data'] as $discussion){
            //$discussion['Name'] = utf8_encode($discussion['Name']);
            //$discussion['Name'] = mb_convert_encoding($discussion['Name'], 'UTF-8', 'ISO-8859-1');
            $discussionRepository->saveDiscussion($discussion['DiscussionID'], $discussion['Name'], $discussion['DateCreated']);
            $dat['new_discussions']++;
        }

        $duration=time()-$start_time;
        $dat['msg']=sprintf("%s url(s) added in %s seconds", $dat['new_urls'], $duration);
        $dat['duration']=$duration;
        return $this->json($dat);
    }


    #[Route('/api/search/{q}', name: 'app_api_search')]
    public function search(string $q, SearchRepository $searchRepository): JsonResponse
    {
        $dat=[];
        $dat['q']=$q;
        $dat['results']=$searchRepository->search($q);
        return $this->json($dat);
    }



    #[Route('/api/crawl/images', methods: ['GET'])]
    public function crawlImages(LinkRepository $linkRepository): JsonResponse
    {
        $dat=[];
        $dat['count']=0;
        $links=$linkRepository->findWaitingImages(5);
        foreach($links as $link){
            $link->getUrl();
            $dat['count']++;
        }
        return $this->json($dat);
    }

    #[Route('/api/crawl/youtube', methods: ['GET'])]
    public function crawlYoutube(LinkRepository $linkRepository, YoutubeService $youtubeService): JsonResponse
    {
        //crawl youtube video, USING the youtube API
        $dat=[];

        if (!isset($_ENV['YOUTUBE_API_KEY'])) {
            $dat['error']='no YOUTUBE_API_KEY';
            return $this->json($dat);
        }

        $dat['count']=0;
        $dat['found']=0;
        $dat['404']=0;
        $links=$linkRepository->findWaitingProvider('youtube', 5);
        foreach($links as $link){

            $url=$link->getUrl();
            //echo "$url";

            $snippet=$youtubeService->fetchSnippet($url);
            //dd($snippet);


            if (count($snippet)) {//Found video
                //$snippet['description']=str_replace('’',"'",$snippet['description']);//accent pourri, DB pas contente
                $snippet['description'] = iconv('UTF-8', 'ASCII//TRANSLIT', $snippet['description']);

                //echo $snippet['description']."<br />";
                // Detect the encoding
                //$dat['encoding'] = mb_detect_encoding($snippet['description'], mb_detect_order(), true);

                $thumbnail_url=$youtubeService->thumbnailUrl($snippet['thumbnails']);
                $link->setStatus(200);
                $link->setTitle($snippet['title']);
                $link->setDescription($snippet['description']);
                if ($thumbnail_url) {
                    $link->setImage($thumbnail_url);
                }
                $dat['found']++;
            }else{
                $link->setStatus(404);//not found
                $link->setTitle('not found');
                $dat[404]++;
            }

            //dd($snippet);
            $link->visited();

            $linkRepository->save($link,true);
            //dd($snippet);
            $dat['count']++;
        }
        $dat['msg']='done';
        return $this->json($dat);
    }

    #[Route('/api/status', methods: ['GET'])]
    public function status(LinkRepository $linkRepository, StatRepository $statRepository): JsonResponse
    {

        $dat=[];//payload
        $dat['time']=date('c');

        $last_records=$linkRepository->findBy([],['created_at' => 'DESC'], 1);
        $last_crawled=$linkRepository->findBy([],['visited_at'=> 'DESC'], 1);

        $dat['last_link_time'] = $last_records[0]->getCreatedAt();
        $dat['last_crawl_time']= $last_crawled[0]->getVisitedAt();
        $dat['last_crawl_ago']= 'todo';

        $dat['countTotal']=$statRepository->countLinks();
        $dat['countVisited']=$statRepository->countVisitedLinks();

        $dat['crawled_pct']=0;

        if ($dat['countVisited']>0 && $dat['countTotal']>0) {
            $dat['crawled_pct']=$dat['countVisited']/$dat['countTotal']*100;
        }

        return $this->json($dat);
    }

    #[Route('/api/link/{id}', methods: ['GET'])]
    public function link(int $id, LinkRepository $linkRepository, SerializerInterface $serializer): JsonResponse
    {
        $link=$linkRepository->find($id);
        if (!$link) {
            return new JsonResponse('{}', 404, [], true);
        }
        $json = $serializer->serialize($link, 'json');
        //dd($json);
        return new JsonResponse($json, 200, [], true);
    }

}
