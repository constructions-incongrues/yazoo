<?php

namespace App\Controller;

use App\Repository\DiscussionRepository;
use App\Repository\LinkRepository;
use App\Repository\SearchRepository;
use App\Repository\StatRepository;
use App\Service\CommentService;
use App\Service\ExtractService;
use App\Service\LinkPreviewService;
use App\Service\MusiqueIncongrueService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

use Embed\Embed;
use Exception;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_job')]
    public function index(RouterInterface $routerInterface): Response
    {
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
        $searchRepository->search($q);
        $data=$searchRepository->getResultPage(1,10);
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

    #[Route('/api/link/{id}', methods: ['DELETE'])]
    public function linkDelete(int $id, LinkRepository $linkRepository): JsonResponse
    {
        //TODO
        $link=$linkRepository->find($id);
        if (!$link) {
            return new JsonResponse('{}', 404, [], true);
        }else{
            $linkRepository->delete($link);
            //$linkRepository->
        }

        $json=[];

        return new JsonResponse($json, 200);
    }

    // Random Link    
    #[Route('/api/random/image', methods: ['GET'])]
    public function randomImage(LinkRepository $linkRepository): JsonResponse
    {
        $link=$linkRepository->findRandomImage();
        return $this->json($link);
    }

    #[Route('/api/random/youtube', methods: ['GET'])]
    public function randomYt(LinkRepository $linkRepository): JsonResponse
    {
        $link=$linkRepository->findRandomYoutube();
        return $this->json($link);
    }

    #[Route('/api/random/gif', methods: ['GET'])]
    public function randomGif(LinkRepository $linkRepository): JsonResponse
    {
        $link=$linkRepository->findRandomGif();
        return $this->json($link);
    }




    #[Route('/api/link/{id}/embed', methods: ['GET'])]
    public function embed(int $id, LinkRepository $linkRepository): JsonResponse
    {
        $link=$linkRepository->find($id);

        $dat=[];

        if ($link) {
            //$preview=new LinkPreviewService($link);
            //$data=$preview->data();
            //return $this->json($data);
            try{
                $embed = new Embed();
                $info=$embed->get($link->getUrl());
                //$info->getResponse()
                //dd($info);
                $dat['title']=(string)$info->title;
                $dat['description']=(string)$info->description;
                $dat['image']=(string)$info->image;
                $dat['code']=(string)$info->code;
                $dat['statusCode']=$info->getResponse()->getStatusCode();
                //$dat['httpcode']=(string)$info->HttpCode;
            }

            catch(Exception $e){
                $dat['error']=$e->getMessage();
                $dat['errorCode']=$e->getCode();
                //$this->logger->warning($e->getMessage(), ['channel'=>'crawler', 'url'=>$url]);
                //return false;
            }
            return $this->json($dat);
        }

        return new JsonResponse('{}', 404, [], true);
    }

    


    #[Route('/api/urlinfo/{url}', methods: ['POST'])]
    public function info(string $url): JsonResponse
    {
        $dat=[];

        try{
            $embed = new Embed();
            $info=$embed->get($url);
            //$info->getResponse()
            //dd($info);
            $dat['title']=(string)$info->title;
            $dat['description']=(string)$info->description;
            $dat['image']=(string)$info->image;
            $dat['code']=(string)$info->code;
            $dat['statusCode']=$info->getResponse()->getStatusCode();
        }

        catch(Exception $e){
            $dat['error']=$e->getMessage();
            $dat['errorCode']=$e->getCode();
        }

        return $this->json($dat);
        //return new JsonResponse('{}', 404, [], true);
    }

}
