<?php

namespace App\Controller;

use App\Repository\BlacklistRepository;
use App\Repository\LinkRepository;
use App\Service\HttpStatusService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Embed\Embed;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class LinkController extends AbstractController
{
    private $linkRepository;
    private $blacklistRepository;

    public function __construct(LinkRepository $linkRepository, BlacklistRepository $blacklistRepository)
    {
        $this->linkRepository=$linkRepository;
        $this->blacklistRepository=$blacklistRepository;
    }

    #[Route('/link/{id}', name: 'app_link_preview')]
    public function index(int $id): Response
    {
        $url='test';
        $link=$this->linkRepository->find($id);

        if (!$link) {
            throw new NotFoundHttpException('The resource was not found.');
        }

        return $this->render('link/index.html.twig', [
            'controller_name' => 'InspectController',
            'id' => $id,
            'link'=>$link,
            'url'=>$link->getUrl(),
            'blacklisted'=>$this->blacklistRepository->isBlacklisted($link->getUrl()),
            'canonical'=>$link->getCanonical(),
            'status'=>$link->getStatus(),
            'discussion_id'=>$link->getDiscussionId(),
            'comment_id'=>$link->getCommentId(),
            //'title'=>$link->getTitle(),
            //'description'=>$link->getDescription(),
            'preview' => $link->getPreview(),
        ]);
    }

    #[Route('/link/{id}/crawl', name: 'app_link_crawl')]
    public function crawl(int $id):Response
    {
        $link=$this->linkRepository->find($id);

        if (!$link) {
            throw new NotFoundHttpException('The resource was not found.');
        }

        try{
            $embed = new Embed();//https://packagist.org/packages/embed/embed
            $info=$embed->get($link->getUrl());
        }

        catch(Exception $e){
            throw new Exception("nope");
        }

        if ($info->code) {
            //var_dump($info->code);exit;
        }


        return $this->render('link/crawl.html.twig', [
            'id' => $id,
            'url' => $info->url,

            'title' => $info->title,
            'description' => (string)$info->description,
            'image' => (string)$info->image,

            'authorName' => $info->authorName,
            'authorUrl' => $info->authorUrl,
            'info' => $info,
        ]);
    }

    #[Route('/link/{id}/delete', name: 'app_link_delete')]
    public function delete(int $id):Response
    {
        $link=$this->linkRepository->find($id);

        if (!$link) {
            throw new NotFoundHttpException('The resource was not found.');
        }

        $this->linkRepository->delete($link);

        return $this->redirect("/");
    }

    #[Route('/link/{id}/embed', name: 'app_link_embed')]
    public function embed(int $id):JsonResponse
    {
        $link=$this->linkRepository->find($id);

        $data=[];//payload

        try{
            $embed = new Embed();//https://packagist.org/packages/embed/embed
            $info=$embed->get($link->getUrl());
            if ($info->code) {
                $data['html']=$info->code->html;
                $data['width']=$info->code->width;
                $data['height']=$info->code->height;
            }else{
                $data['message']='no embed code';
            }
        }

        catch(Exception $e){
            throw new Exception("nope");
        }
        return $this->json($data);
    }

    #[Route('/link/{id}/status', name: 'app_link_status')]
    public function status(int $id):JsonResponse
    {
        $httpservice=new HttpStatusService();
        $link=$this->linkRepository->find($id);
        $data=[];//payload
        $data['url']=$link->getUrl();
        $data['status']=$httpservice->getHttpCode($data['url']);
        $data['info']=$httpservice->codeName($data['status']);
        return $this->json($data);
    }


}
