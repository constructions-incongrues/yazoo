<?php

namespace App\Controller;

use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExtractController extends AbstractController
{
    #[Route('/extract', name: 'app_extract')]
    public function index(): Response
    {
        return $this->render('extract/index.html.twig', [
            'controller_name' => 'ExtractController',
        ]);
    }

    #[Route('/extract/video', name: 'app_extract_video')]
    public function extractVideo(LinkRepository $linkRepository):JsonResponse
    {
        $links=$linkRepository->findVideos();
        $data=[];//payload
        foreach ($links as $link) {
            $data[] = [
                'id' => $link->getId(),
                'url' => $link->getUrl(),
                'status' => $link->getStatus(),
                'title' => $link->getTitle(),
                'description' => $link->getDescription(),
                'discussion_id' => $link->getDiscussionId(),
                'comment_id' => $link->getCommentId(),
            ];
        }
        return $this->json($data);
    }

    #[Route('/extract/images', name: 'app_extract_images')]
    public function extractImages(LinkRepository $linkRepository):JsonResponse
    {
        $links=$linkRepository->findImages();
        $data=[];//payload
        foreach ($links as $link) {
            $data[] = [
                'id' => $link->getId(),
                'url' => $link->getUrl(),
                'status' => $link->getStatus(),
                'discussion_id' => $link->getDiscussionId(),
                'comment_id' => $link->getCommentId(),
                //'comment_id' => $link->getDescription(),

            ];
        }
        return $this->json($data);

    }
}
