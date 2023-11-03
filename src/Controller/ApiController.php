<?php

namespace App\Controller;

use App\Repository\DiscussionRepository;
use App\Repository\LinkRepository;
use App\Repository\SearchRepository;
use App\Repository\StatRepository;
use App\Service\CommentService;
use App\Service\ExtractService;
use App\Service\MusiqueIncongrueService;

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



        $dat=[];//payload
        $dat['start_time']=time();
        // 1 - Sync Links
        $dat['new_urls']=0;
        $data=$MI->fetchComments();//Fetch From Directus/MusiqueIncongrues
        foreach($data['data'] as $r){
            $r['urls']=$extractService->extractUrls($r['Body']);
            if (count($r['urls'])) {
                $linkRepository->saveUrls($r['urls'], $r['CommentID'], $r['DiscussionID'], $r['AuthUserID']);
                $dat['new_urls']++;
            }
        }

        // 2 - Sync Discussions
        $dat['new_discussions']=0;
        $discussions=$MI->fetchDiscussions();//Fetch From Directus/MusiqueIncongrues
        foreach($discussions['data'] as $discussion){
            $discussionRepository->saveDiscussion($discussion['DiscussionID'], $discussion['Name'], $discussion['DateCreated']);
            $dat['new_discussions']++;
        }

        $dat['exec_time']=time()-$dat['start_time'];
        $dat['msg']=sprintf("%s url(s) added in %s seconds", $dat['new_urls'], $dat['exec_time']);

        return $this->json($dat);
    }


    #[Route('/api/search/{q}', name: 'app_api_search')]
    public function search(string $q, SearchRepository $searchRepository): JsonResponse
    {
        $data=$searchRepository->search($q);
        return $this->json($data);
    }







    #[Route('/api/link', methods: ['GET'])]
    public function link(): JsonResponse
    {
        $dat=[];
        $dat['error']=404;
        $dat['usage']='/api/link/id';
        return new JsonResponse($dat, 404);
    }

    #[Route('/api/link/{id}', methods: ['GET'])]
    public function linkId(int $id, LinkRepository $linkRepository, SerializerInterface $serializer): JsonResponse
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
