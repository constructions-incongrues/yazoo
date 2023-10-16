<?php
## Stats

//- Count By status code
//- Count by filetype (extension)
//- Count By Users
//- Number of youtube Videos

namespace App\Controller;

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


    #[Route('/contributors', name: 'app_contributors')]
    public function data(StatRepository $statRepository): Response
    {
        return $this->render('stats/contributors.html.twig', [
            'contributors' => $statRepository->contributors(),
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
