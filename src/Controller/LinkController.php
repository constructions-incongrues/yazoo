<?php

namespace App\Controller;

use App\Repository\BlacklistRepository;
use App\Repository\DiscussionRepository;
use App\Repository\LinkRepository;
use App\Service\HttpStatusService;
use App\Service\LinkPreviewService;

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
    private $discussionRepository;
    private $blacklistRepository;

    public function __construct(LinkRepository $linkRepository, DiscussionRepository $discussionRepository, BlacklistRepository $blacklistRepository)
    {
        $this->linkRepository=$linkRepository;
        $this->discussionRepository=$discussionRepository;
        $this->blacklistRepository=$blacklistRepository;

    }

    #[Route('/link/{id}', name: 'app_link_preview')]
    public function index(int $id): Response
    {
        //$url='test';
        $link=$this->linkRepository->find($id);

        if (!$link) {
            throw new NotFoundHttpException('The resource was not found.');
        }

        //$preview=new urlPreviewService($link->getUrl());

        $preview=new LinkPreviewService($link);

        $last_visit='';
        if ($link->getVisitedAt()) {
            $last_visit=$link->getVisitedAt()->format("Y-m-d H:i:s");
        }

        $discussion_name='';
        if ($link->getDiscussionId()) {
            $discussion_name=$this->discussionRepository->getName($link->getDiscussionId());
        }

        return $this->render('link/index.html.twig', [
            'id' => $id,
            'link'=>$link,
            'url'=>$link->getUrl(),
            'blacklisted'=>$this->blacklistRepository->isBlacklisted($link->getUrl()),
            'canonical'=>$link->getCanonical(),
            'status'=>$link->getStatus(),
            'discussion_id'=>$link->getDiscussionId(),
            'discussion'=>$discussion_name,
            'comment_id'=>$link->getCommentId(),
            'title'=>$link->getTitle(),
            'description'=>$link->getDescription(),
            'preview' => $preview->data(),
            'visited_at'=>$last_visit,
        ]);
    }
    
}
