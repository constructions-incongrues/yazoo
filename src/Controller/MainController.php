<?php

namespace App\Controller;

use App\Service\CommentService;
use App\Service\DatabaseService;

use Embed\Embed;
use Exception;
//use Embed\Http\Crawler;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }



  

}
