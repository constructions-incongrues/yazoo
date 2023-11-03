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
        $page=1;

        if (@$_GET['q']) {
            $q=$_GET['q'];
        }

        if (@$_GET['page']>0) {
            $page=$_GET['page'];
        }

        //dd($_GET);

        $data=$this->searchRepository->search($q, $page, 15);

        //$this->loggerInterface->log('log', $q);//TODO Log search

        return $this->render('search/index.html.twig', [
            //'q' => $q,
            'data' => $data,
        ]);
    }

    #[Route('/test')]
    public function testes(): Response
    {
        $page=1;
        if (@$_GET['page']>0) {
            $page=$_GET['page'];
        }

        $paginator=$this->searchRepository->searchTest("test", $page, 10);

        $dat=[];
        $dat['count']=count($paginator);
        $dat['test']='yo';
        $dat['results']=$paginator;
        /*
        foreach($paginator as $item){
            dd($item);
        }
        */
        return $this->json($dat);
    }
}
