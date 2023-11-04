<?php

namespace App\Controller;

use App\Repository\SearchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImagesController extends AbstractController
{

    private $searchRepository;
    public function __construct(SearchRepository $searchRepository)
    {
        $this->searchRepository=$searchRepository;
    }


    #[Route('/images', name: 'app_images')]
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

        $this->searchRepository->searchImages($q);
        $this->searchRepository->filterStatusError();//exclude 404, etc
        $data=$this->searchRepository->getResultPage($page, 30);
        return $this->render('images/index.html.twig', [
            'data' => $data,
        ]);
    }
}
