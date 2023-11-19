<?php

namespace App\Service;

use App\Entity\Link;
use App\Repository\BlacklistRepository;
use App\Repository\LinkRepository;

use App\Service\HttpStatusService;

use Embed\Embed;
use Exception;
use Psr\Log\LoggerInterface;

class CrawlService
{
    private $linkRepository;
    private $blacklistRepository;
    private $httpStatusService;
    private $logger;

    public function __construct(LinkRepository $linkRepository, BlacklistRepository $blacklistRepository, HttpStatusService $HttpStatusService, LoggerInterface $logger)
    {
        $this->linkRepository = $linkRepository;
        $this->blacklistRepository = $blacklistRepository;
        $this->httpStatusService = $HttpStatusService;
        $this->logger = $logger;
    }

    public function crawlLink(Link $link)
    {
        $url=$link->getUrl();

        // Check against blacklist
        if ($this->blacklistRepository->isBlacklisted($url)) {
            $this->linkRepository->delete($link);
            return false;
        }

        $status=$this->httpStatusService->get($url);
        $link->visited();
        $link->setStatus($status['httpStatus']);
        $link->setMimetype($status['mimeType']);
        $this->linkRepository->save($link,true);//Save once

        if ($status['httpStatus']==0) {// Unreachable
            $this->logger->info("Unreachable URL", ['channel'=>'crawler', 'url'=>$url]);
        }

        if ($status['httpStatus']>=200 && $status['httpStatus']<400) {

            try{
                $embed = new Embed();
                $info=$embed->get($url);
            }

            catch(Exception $e){
                //dd($e->getMessage());
                $this->logger->warning($e->getMessage(), ['channel'=>'crawler', 'url'=>$url]);
                return false;
            }

            $statusCode=$info->getResponse()->getStatusCode();

            if ($statusCode) {
                $link->setStatus($statusCode);
            }

            if ($info->title) {
                $link->setTitle((string)$info->title);
            } else {
                $link->setTitle(basename($url));
            }

            $link->setDescription((string)$info->description);

            $link->setCanonical((string)$info->url);

            if ($info->image) {
                $link->setImage((string)$info->image);
            }

            $link->setProvider((string)$info->providerName);


            //Fix 301 that are 404
            //Todo -> make a factory about it
            if (preg_match("/\b(404|page not found)\b/i", $link->getTitle())) {
                //$io->warning("404 detected in title : ".$link->getTitle());
                $link->setStatus(404);
            }
            
            if (preg_match("/^Se connecter à Facebook$/i", $link->getTitle())) {
                $link->setStatus(404);
            }
            
        }


        $this->linkRepository->save($link,true);
        return $link;
    }
}