<?php

namespace App\Service;

use App\Entity\Link;
use Embed\Embed;
use Exception;


class LinkPreviewService
{
    private $link;
    private $host;
    private $filename;
    private $extension;

    public function __construct(Link $link)
    {

        $this->link=$link;

        $parsed=parse_url($this->link->getUrl());
        
        //dd($parsed);
        /*
        if (!empty($parsed['path'])) {
            $this->filename=basename($parsed['path']);
        }
        */
        $this->filename=$link->getFilename();
        $this->host=$link->getHost();
        $this->extension = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
    }

    public function vimeoPreview(): string
    {
        //dd($this->link);
        $vimeo_id=null;
        if (preg_match("/\/([0-9]{6,})/i", $this->link->getUrl(), $o)) {
            $vimeo_id=$o[1];
        }

        if ($vimeo_id) {
            $url='https://player.vimeo.com/video/'.$vimeo_id;
            $html='<iframe src="'.$url.'" width="100%" height="380" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
        }else{
            $html="Vimeo ID not found";
        }

        return $html;
    }

    public function dailymotion(): string
    {
        //$url='https://www.dailymotion.com/video/x5zrci';
        //$url=$this->link->getCanonical();

        $id='';

        if (preg_match("/\/([a-z0-9]{6,7})/i", $this->link->getUrl(), $o)) {
            $id=$o[1];
        }

        $url='https://www.dailymotion.com/video/' . $id;

        $html='<iframe width="100%" height="380" frameborder="0" type="text/html" src="https://www.dailymotion.com/embed/video/x857y8?autoplay=1" width="100%" height="100%" allowfullscreen src="'.$url.'"></iframe>';
        return $html;
    }

    public function youtubePreview(): string
    {

        $URL=$this->link->getUrl();
        $youtube_id='';//xKQskYS18vI
        if (preg_match("/youtu\.be\/([0-9a-z_-]{11})/i", $URL, $o)) {
            //print_r($o);
            $youtube_id=$o[1];
        } else if(preg_match("/v=([0-9a-z_-]{11})/i",$URL, $o)) {

            if ($o[1]) {
                $youtube_id=$o[1];
            }

        } else {
            //TODO log
            //throw new Exception("Error Detecting youtube key: $URL", 1);
            return '';
        }

        //dd($this->url,$youtube_id);

        //<!-- YouTube Video Embed -->
        $htm='<iframe width="100%" height="315" src="https://www.youtube.com/embed/'.$youtube_id.'" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
        return $htm;

    }
    public function audioPreview(): string
    {
        //dd($this->extension);
        $htm='';
        switch (strtolower($this->extension)) {

            case 'mp3':
            case 'ogg':
                $htm='<audio controls><source src="'.htmlentities($this->link->getUrl()).'"></audio>';
                break;
        }
        return $htm;
    }


    public function imagePreview(): string
    {
        $htm='<img src="'.htmlentities($this->link->getUrl()).'">';
        return $htm;
    }

    public function data()
    {
        $dat=[];
        $dat['title']=$this->link->getTitle();
        $dat['description']=$this->link->getDescription();
        $dat['status']=$this->link->getStatus();
        $dat['provider']=$this->link->getProvider();
        $dat['image']=$this->link->getImage();
        $dat['html']=null;

        $url=$this->link->getUrl();

        //dd($this->url, $this->host, $this->extension);
        $HOST=$this->link->getHost();
        
        switch($HOST){
            
            case "fr.youtube.com":
            case "m.youtube.com":
            case "www.youtube.com":
            case "youtu.be":            
                $dat['html']=$this->youtubePreview();
                break;

            case "www.vimeo.com":
            case "vimeo.com":
                $dat['html']=$this->vimeoPreview();
                break;

            case "www.dailymotion.com":
            case "dailymotion.com":
                $dat['html']=$this->dailymotion();
                break;

                /*
            case "www.mixcloud.com":
                $dat['html']="image:".$dat['image'];
                break;
            */

            default:
                //dd($HOST);
                break;
        }

        switch($this->extension){
            case "gif":
            case "png":
            case "jpg":
            case "webm":
                $dat['html']=$this->imagePreview();

            case "mp3":
            case "ogg":
                $dat['html']=$this->audioPreview();
                break;
        }

        if (!$dat['html'] && $dat['image']) {
            $dat['html']=sprintf('<img src="%s" alt="%s">',$dat['image'],$dat['title']);
        }

        return $dat;
    }

}