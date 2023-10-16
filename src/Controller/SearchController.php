<?php

namespace App\Controller;

use App\Repository\SearchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{

    private $searchRepository;
    public function __construct(SearchRepository $searchRepository)
    {
        $this->searchRepository=$searchRepository;
    }

    #[Route('/', name: 'app_search_landing')]
    public function index(): Response
    {
        $q='';

        if (@$_GET['q']) {
            $q=$_GET['q'];
        }

        $data=$this->searchRepository->search($q, 1, 30);

        return $this->render('search/index.html.twig', [
            'q' => $q,
            'results' => $data['results'],
            'count' => $data['count'],
        ]);
    }

}
