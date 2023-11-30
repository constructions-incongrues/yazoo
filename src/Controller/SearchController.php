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

    #[Route('/', name: 'app_landing')]
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

        //dd($_GET);

        $this->searchRepository->search($q);
        $this->searchRepository->filterStatusError();
        $data=$this->searchRepository->getResultPage($page, 10);
        //$this->loggerInterface->log('log', $q);//TODO Log search

        return $this->render('search/index.html.twig', [
            'data' => $data,
        ]);
    }

}
