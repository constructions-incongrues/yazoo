<?php

namespace App\Controller;

use App\Repository\DiscussionRepository;
use App\Repository\LinkRepository;
use App\Service\CommentService;
use App\Service\ExtractService;
use App\Service\MusiqueIncongrueService;
use App\Service\YoutubeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_job')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'JobController',
        ]);
    }

    #[Route('/api/sync', name: 'app_job_sync')]
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

    #[Route('/api/test', name: 'app_job_test')]
    public function test(MusiqueIncongrueService $MI, DiscussionRepository $discussionRepository): JsonResponse
    {
        $dat=[];
        $dat['count']=0;
        $discussions=$MI->fetchDiscussions();//Fetch From Directus/MusiqueIncongrues
        $dat['discussions']=$discussions['data'];
        //dd($dat['discussions']);
        return $this->json($dat);
    }

    #[Route('/api/images', name: 'app_job_crawl_images')]
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

    #[Route('/api/youtube', name: 'app_job_youtube')]
    public function crawlYoutube(LinkRepository $linkRepository, YoutubeService $youtubeService): JsonResponse
    {
        //crawl youtube video, USING the youtube API
        $dat=[];
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

}
