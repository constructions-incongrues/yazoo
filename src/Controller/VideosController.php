<?php

namespace App\Controller;

use App\Repository\SearchRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VideosController extends AbstractController
{

    private $searchRepository;
    public function __construct(SearchRepository $searchRepository)
    {
        $this->searchRepository=$searchRepository;
    }


    #[Route('/videos', name: 'app_video')]
    public function index(): Response
    {
        $q='';

        if (@$_GET['q']) {
            $q=$_GET['q'];
        }

        $data=$this->searchRepository->searchVideos($q, 1, 30);

        return $this->render('videos/index.html.twig', [
            'q' => $q,
            'results' => $data['results'],
            'count' => $data['count'],
        ]);
    }
}
