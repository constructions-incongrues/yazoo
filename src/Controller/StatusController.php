<?php

namespace App\Controller;

use App\Repository\LinkRepository;
use App\Repository\StatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends AbstractController
{

    private $linkRepository;
    private $statRepository;

    public function __construct(LinkRepository $linkRepository,StatRepository $statRepository)
    {
        $this->linkRepository=$linkRepository;
        $this->statRepository=$statRepository;
    }


    #[Route('/status', name: 'app_status')]
    public function index(): Response
    {
        //Get last crawled records
        //Get newest links
        //Get progress

        $last_records=$this->linkRepository->findBy([],['created_at' => 'DESC'], 10);
        $last_crawled=$this->linkRepository->findBy([],['visited_at'=> 'DESC'], 10);
        //dd($last_crawled);

        $countTotal=$this->statRepository->countLinks();
        $countVisited=$this->statRepository->countVisitedLinks();
        //exit("$countTotal / $countVisited");
        $pct=0;
        if ($countVisited>0 && $countTotal>0) {
            $pct=$countVisited/$countTotal*100;
        }


        return $this->render('status/index.html.twig', [
            'last_link_time' => $last_records[0]->getCreatedAt(),
            'last_crawl_time' => $last_crawled[0]->getVisitedAt(),
            'last_records' => $last_records,
            'last_crawled' => $last_crawled,
            'total_links' => $countTotal,
            'total_visited' => $countVisited,
            'progress_pct' => round($pct,2),
        ]);
    }

    #[Route('/api/status', methods: ['GET'])]
    public function status(LinkRepository $linkRepository, StatRepository $statRepository): JsonResponse
    {

        $dat=[];//payload
        $dat['time']=date('c');

        $last_records=$linkRepository->findBy([],['created_at' => 'DESC'], 1);
        $last_crawled=$linkRepository->findBy([],['visited_at'=> 'DESC'], 1);

        $dat['last_link_time'] = $last_records[0]->getCreatedAt();
        $dat['last_crawl_time']= $last_crawled[0]->getVisitedAt();
        $delta=time()-strtotime($last_crawled[0]->getVisitedAt()->format("Y-m-d H:i:s"));

        if ($delta>3600) {
            $dat['warning']= 'Crawler seems to be stuck. Delta is '.$delta;
        }else{
            $dat['status']= 'ok';
        }

        $dat['countTotal']=$statRepository->countLinks();
        $dat['countVisited']=$statRepository->countVisitedLinks();

        $dat['crawled_pct']=0;

        if ($dat['countVisited']>0 && $dat['countTotal']>0) {
            $dat['crawled_pct']=$dat['countVisited']/$dat['countTotal']*100;
        }

        return $this->json($dat);
    }
}
