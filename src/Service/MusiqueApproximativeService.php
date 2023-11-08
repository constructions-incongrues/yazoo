<?php

namespace App\Service;

use App\Repository\DiscussionRepository;
use App\Repository\LinkRepository;
use Exception;

class MusiqueApproximativeService
{

    /**
     * From .ENV
     *
     * @var [type]
     */
    private $directus_email;
    private $directus_password;
    private $token=null;

    private $linkRepository;
    private $discussionRepository;

    public function __construct(LinkRepository $linkRepository)
    {
        $this->linkRepository=$linkRepository;
        /*
        if (isset($_ENV['DIRECTUS_EMAIL'])) {
            $this->directus_email=$_ENV['DIRECTUS_EMAIL'];
        }

        if (isset($_ENV['DIRECTUS_PASSWORD'])) {
            $this->directus_password=$_ENV['DIRECTUS_PASSWORD'];
        }
        */
    }


}
