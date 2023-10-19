<?php

namespace App\Controller;

use App\Repository\SearchRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{

    private $searchRepository;
    private $loggerInterface;

    public function __construct(SearchRepository $searchRepository, LoggerInterface $loggerInterface)
    {
        $this->searchRepository=$searchRepository;
        $this->loggerInterface=$loggerInterface;
    }

    #[Route('/', name: 'app_search_landing')]
    public function index(): Response
    {
        $q='';

        if (@$_GET['q']) {
            $q=$_GET['q'];
        }

        $data=$this->searchRepository->search($q, 1, 30);


        //$this->loggerInterface->log('log', $q);//TODO

        return $this->render('search/index.html.twig', [
            'q' => $q,
            'results' => $data['results'],
            'count' => $data['count'],
        ]);
    }

}
