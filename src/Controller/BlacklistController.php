<?php

namespace App\Controller;

use App\Repository\BlacklistRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlacklistController extends AbstractController
{
    private $blacklistRepository;
    public function __construct(BlacklistRepository $blacklistRepository)
    {
        $this->blacklistRepository = $blacklistRepository;
    }

    #[Route('/blacklist', name: 'app_blacklist')]
    public function index(): Response
    {
        $records=$this->blacklistRepository->findAll();

        return $this->render('blacklist/index.html.twig', [
            'controller_name' => 'BlacklistController',
            'records' => $records,
        ]);
    }

    #[Route('/blacklist/add/{host}', name: 'app_blacklist_add')]
    public function addHost(string $host): Response
    {

        $blacklist=$this->blacklistRepository->add($host);

        return $this->redirect("/blacklist/");

    }

    #[Route('/blacklist/item/{id}', name: 'app_blacklist_item')]
    public function item(int $id): Response
    {

        $item=$this->blacklistRepository->find($id);

        return $this->render('blacklist/item.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('/blacklist/delete/{id}', name: 'app_blacklist_delete')]
    public function delete(int $id): Response
    {
        $entity=$this->blacklistRepository->find($id);

        $this->blacklistRepository->delete($entity);

        return $this->redirect("/blacklist");
    }
}
