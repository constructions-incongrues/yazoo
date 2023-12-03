<?php

namespace App\Controller;

use App\Service\TelocheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TelocheController extends AbstractController
{
    #[Route('/teloche', name: 'app_teloche')]
    public function index(): Response
    {
        return $this->render('teloche/index.html.twig', [
            'controller_name' => 'TelocheController',
        ]);

    }

    #[Route('/teloche/videos', name: 'app_teloche_videos')]
    public function videos(): Response
    {
        return $this->render('teloche/videos.html.twig', [
            'controller_name' => 'TelocheController',
        ]);

    }

    #[Route('/teloche/notifications', name: 'app_teloche_notifications')]
    public function notifs(): Response
    {
        return $this->render('teloche/notifications.html.twig', [
            'controller_name' => 'TelocheController',
        ]);
    }



    #[Route('/api/teloche/authenticate', name: 'api_teloche_auth', methods: ['POST'])]
    public function getToken(TelocheService $telocheService): Response
    {
        $dat=$telocheService->authenticate();
        return $this->json($dat);
    }
}
