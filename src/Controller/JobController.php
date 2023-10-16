<?php

namespace App\Controller;

use App\Repository\DiscussionRepository;
use App\Repository\LinkRepository;
use App\Service\CommentService;
use App\Service\ExtractService;
use App\Service\MusiqueIncongrueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JobController extends AbstractController
{
    #[Route('/job', name: 'app_job')]
    public function index(): Response
    {
        return $this->render('job/index.html.twig', [
            'controller_name' => 'JobController',
        ]);
    }


    #[Route('/job/sync', name: 'app_job_sync')]
    public function sync(LinkRepository $linkRepository, DiscussionRepository $discussionRepository, MusiqueIncongrueService $MI, ExtractService $extractService): JsonResponse
    {
        $start_time=time();
        $data=$MI->fetchComments();//Fetch From Directus/MusiqueIncongrues
        //var_dump($data['data']);

        $dat=[];//payload
        $dat['new_urls']=0;
        foreach($data['data'] as $r){
            $r['urls']=$extractService->extractUrls($r['Body']);
            if (count($r['urls'])) {
                $linkRepository->saveUrls($r['urls'], $r['CommentID'], $r['DiscussionID'], $r['AuthUserID']);
                $dat['new_urls']++;
            }
        }


        //Fetch Discussions
        $dat['new_discussions']=0;
        $discussions=$MI->fetchDiscussions();//Fetch From Directus/MusiqueIncongrues
        //$dat['discussions']=$discussions['data'];
        foreach($discussions['data'] as $discussion){
            $discussionRepository->saveDiscussion($discussion['DiscussionID'], $discussion['Name'], $discussion['DateCreated']);
            $dat['new_discussions']++;
        }

        $duration=time()-$start_time;
        $dat['msg']=sprintf("%s url(s) added in %s seconds", $dat['new_urls'], $duration);
        $dat['duration']=$duration;
        return $this->json($dat);
    }

    #[Route('/job/test', name: 'app_job_test')]
    public function test(MusiqueIncongrueService $MI, DiscussionRepository $discussionRepository): JsonResponse
    {
        $dat=[];
        $dat['count']=0;
        $discussions=$MI->fetchDiscussions();//Fetch From Directus/MusiqueIncongrues
        $dat['discussions']=$discussions['data'];
        foreach($dat['discussions'] as $discussion){
            $discussionRepository->saveDiscussion($discussion['DiscussionID'], $discussion['Name'], $discussion['DateCreated']);
            $dat['count']++;
        }

        return $this->json($dat);
    }

    #[Route('/job/crawl', name: 'app_job_crawl')]
    public function crawl(): JsonResponse
    {
        $dat=[];
        $dat['TODO']='todo';
        return $this->json($dat);
    }

}
