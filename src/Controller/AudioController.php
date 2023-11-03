<?php

namespace App\Controller;

use App\Repository\SearchRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AudioController extends AbstractController
{
    private $searchRepository;

    public function __construct(SearchRepository $searchRepository)
    {
        $this->searchRepository=$searchRepository;
    }


    #[Route('/audio', name: 'app_audio')]
    public function index(): Response
    {
        $q='';

        $page=1;

        if (@$_GET['q']) {
            $q=$_GET['q'];
        }

        if (@$_GET['page']>0) {
            $page=$_GET['page'];
        }

        $this->searchRepository->searchAudio($q);
        $data=$this->searchRepository->getResultPage($page, 30);

        return $this->render('audio/index.html.twig', [
            'data' => $data,
        ]);
    }
}
