<?php

namespace App\Service;

use App\Entity\Link;
use App\Repository\BlacklistRepository;
use App\Repository\LinkRepository;

use App\Service\HttpStatusService;

use Embed\Embed;
use Exception;


class CrawlService
{
    private $linkRepository;
    private $blacklistRepository;
    private $httpStatusService;

    public function __construct(LinkRepository $linkRepository, BlacklistRepository $blacklistRepository, HttpStatusService $HttpStatusService)
    {
        $this->linkRepository = $linkRepository;
        $this->blacklistRepository = $blacklistRepository;
        $this->httpStatusService = $HttpStatusService;
    }

    public function crawlLink(Link $link)
    {
        $url=$link->getUrl();
        //$dat['urls'][]=$url;

        // Check against blacklist
        if ($this->blacklistRepository->isBlacklisted($url)) {
            $this->linkRepository->delete($link);
            return false;
        }

        $status=$this->httpStatusService->get($url);

        //echo "$url";
        //dd($status);

        $link->setStatus($status['httpStatus']);
        $link->setMimetype($status['mimeType']);

        if ($status['httpStatus']==0) {// Unreachable
            // TODO Log to DB
            //$this->logger->warning("Unreachable URL",['channel'=>'crawler', 'url'=>$url]);
        }

        if ($status['httpStatus']>=200 && $status['httpStatus']<400) {

            try{
                $embed = new Embed();
                $info=$embed->get($url);
            }

            catch(Exception $e){
                dd($e->getMessage());
                //$this->logger->warning($e->getMessage(),['channel'=>'crawler', 'url'=>$url]);
                return false;
            }

            //$meta=[];

            if($info->title){
                //$meta['title']=$info->title; //The page title
                $link->setTitle((string)$info->title);
            }else{
                $link->setTitle(basename($url));
            }

            //$meta['description']=$info->description; //The page description
            $link->setDescription((string)$info->description);
            //$meta['canonical']=(string)$info->url; //The canonical url
            $link->setCanonical((string)$info->url);
            //$meta['keywords']=$info->keywords; //The page keywords
            //$meta['image']=(string)$info->image;
            if ($info->image) {
                //TODO check URL length and content
                //$link->setImage($info->image);
            }

            //$meta['lang']=$info->language; //The language of the page
            //$meta['provider']=$info->providerName; //The provider name of the page (Youtube, Twitter, Instagram, etc)
            $link->setProvider((string)$info->providerName);

            //print_r($meta);

            //Fix 301 that are 404
            //Todo -> make a factory about it
            if (preg_match("/\b(404|page not found)\b/i", $link->getTitle())) {
                //$io->warning("404 detected in title : ".$link->getTitle());
                $link->setStatus(404);
            }

        }

        $link->visited();
        $this->linkRepository->save($link,true);
        return $link;
    }
}