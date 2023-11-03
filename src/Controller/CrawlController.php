<?php

namespace App\Controller;

use App\Repository\BlacklistRepository;
use App\Repository\LinkRepository;
use App\Repository\SearchRepository;

use App\Service\YoutubeService;
use App\Service\CrawlService;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CrawlController extends AbstractController
{
    private $linkRepository;
    private $blacklistRepository;
    private $crawlService;

    public function __construct(BlacklistRepository $blacklistRepository, LinkRepository $linkRepository, CrawlService $crawlService)
    {
        $this->linkRepository = $linkRepository;
        $this->blacklistRepository = $blacklistRepository;
        $this->crawlService = $crawlService;
    }

    #[Route('/api/crawl/', name: 'app_crawl')]
    public function crawl(SearchRepository $searchRepository): JsonResponse
    {
        $dat['start_time']=time();
        $searchRepository->search('orderby:crawler');
        $data=$searchRepository->getResultPage(1,5);
        foreach($data['results'] as $link)
        {
            $link=$this->crawlService->crawlLink($link);
            $dat['urls'][]=$link->getUrl();
        }
        $dat['exec_time']=time()-$dat['start_time'];
        return $this->json($dat);
    }

    #[Route('/api/crawl/audio', name: 'app_crawl_audio')]
    public function audio(SearchRepository $searchRepository): JsonResponse
    {
        $dat=[];
        $searchRepository->searchAudio('orderby:crawler');
        $data=$searchRepository->getResultPage(1,5);
        foreach($data['results'] as $link)
        {
            $link=$this->crawlService->crawlLink($link);
            $dat['urls'][]=$link->getUrl();
        }
        return $this->json($dat);
    }


    #[Route('/api/crawl/images', methods: ['GET'])]
    public function crawlImages(SearchRepository $searchRepository): JsonResponse
    {
        $dat=[];
        $dat['start_time']=time();
        $dat['urls']=[];
        //$dat['count']=0;

        $searchRepository->searchImages('orderby:crawler');
        $data=$searchRepository->getResultPage(1,5);

        //dd($data);

        foreach($data['results'] as $link)
        {
            $link=$this->crawlService->crawlLink($link);
            if ($link) {
               $url=$link->getUrl();
                $dat['urls'][]=$url;
            }
        }
        $dat['exec_time']=time()-$dat['start_time'];
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
                //Could be deleted ?
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
}
