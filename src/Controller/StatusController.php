<?php

namespace App\Controller;

use App\Repository\LinkRepository;
use App\Repository\StatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

        $last_records=$this->linkRepository->findBy([],['created_at' => 'DESC'], 20);
        $last_crawled=$this->linkRepository->findBy([],['visited_at'=> 'DESC'], 20);
        //dd($last_crawled);

        $countTotal=$this->statRepository->countLinks();
        $countVisited=$this->statRepository->countVisitedLinks();
        //exit("$countTotal / $countVisited");
        $pct=$countVisited/$countTotal*100;

        return $this->render('status/index.html.twig', [
            'last_link_time' => $last_records[0]->getCreatedAt(),
            'last_crawl_time' => $last_crawled[0]->getVisitedAt(),
            'last_records' => $last_records,
            'last_crawled' => $last_crawled,
            'total_links' => $countTotal,
            'total_visited' => $countVisited,
            'progress_pct' => round($pct),
        ]);
    }
}
