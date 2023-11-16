<?php
## Stats

//- Count By status code
//- Count by filetype (extension)
//- Count By Users
//- Number of youtube Videos

namespace App\Controller;

use App\Repository\DiscussionRepository;
use App\Repository\StatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'app_stats')]
    public function index(StatRepository $statRepository): Response
    {

        return $this->render('stats/index.html.twig', [

            'providers' => $statRepository->providers(),
            'statuscodes' => $statRepository->statusCodes(),
            'mimetypes' => $statRepository->mimetypes(),
            'count' => $statRepository->countLinks(),
        ]);
    }


    #[Route('/stats/contributors', name: 'app_contributors')]
    public function contributors(StatRepository $statRepository): Response
    {
        return $this->render('stats/contributors.html.twig', [
            'contributors' => $statRepository->contributors(),
        ]);
    }


    #[Route('/stats/discussions', name: 'app_discussions')]
    public function discussions(StatRepository $statRepository, DiscussionRepository $discussionRepository): Response
    {
        $stats=$statRepository->discussions();
        foreach($stats as &$stat){
            if ($stat['discussion_id']) {
                $stat['name']=$discussionRepository->getName($stat['discussion_id']);
            }else{
                $stat['name']='';
            }

        }

        return $this->render('stats/discussions.html.twig', [
            'discussions' => $stats,
        ]);
    }



    #[Route('/stats/provider/{provider}', name: 'app_stats_provider')]
    public function provider(string $provider, StatRepository $statRepository): Response
    {
        return $this->render('stats/provider.html.twig', [
            'provider' => $provider,
            'statuscodes' => $statRepository->statusCodes($provider),
            'mimetypes' => $statRepository->mimetypes($provider),
        ]);
    }


    #[Route('/statsdata', name: 'app_providers')]
    public function datatest(StatRepository $statRepository):JsonResponse
    {
        //print_r($statRepository->statusCodes());
        return $this->json([
            'providers' => $statRepository->providers(),
            'contibutors' => $statRepository->contributors(),
            'statuscodes' => $statRepository->statusCodes(),
            'mimetypes' => $statRepository->mimetypes(),
            //'progress'=>
        ]);
    }


}
