<?php

namespace App\Service;

/**
 * Methods to extract links from text (forum posts)
 */
class ExtractService
{

    /**
     * Extract a list of url strings from a given text
     * Focus on bbcode URL's first, and match stray urls last
     * @param string $text
     * @return array
     */
    public function extractUrls(string $text): array
    {
        //echo $text;
        //$pattern = '/\bhttps?:\/\/\S+\b/';
        //[url]http://dreynaline.free.fr/[/url] [img]http://dreynaline.free.fr/images/recto.jpg[/img]

        $reg_bblinks='/\[(img|url)\](.*?)\[\/(img|url)\]/i';

        $urls=[];

        preg_match_all($reg_bblinks, $text, $o);

        for($i=0;$i<count($o[0]);$i++){
            $match=$o[0][$i];
            $url=$o[2][$i];
            $text=str_replace($match,'', $text);//remove matches
            $urls[]=$url;
        }

        //[url=http://www.youtube.com/watch?v=0a1VMkeGkZs]

        $reg2='/\[url=(http.*?)\]/i';
        preg_match_all($reg2, $text, $o);
        for($i=0;$i<count($o[0]);$i++){
            $match=$o[0][$i];
            $url=$o[1][$i];
            $text=str_replace($match,'', $text);//remove matches
            $urls[]=$url;
        }

        //Finaly, match/catch leftovers
        $pattern = '/\bhttps?:\/\/\S+\b/';//minimal detection pattern
        preg_match_all($pattern, $text, $o);
        foreach($o[0] as $url){
            $text=str_replace($url,'', $text);//remove matches
            $urls[]=$url;
        }

        //echo "<li>leftovers:$text";

        //Make some cleaning
        foreach($urls as $k=>$url){
            $urls[$k]=$this->httpsFix($url);//add https for known providers
            $urls[$k]=$this->sanitize($url);//clear url string
        }

        return array_unique($urls);
    }


    /**
     * Filter out Emoji's or unwanted links
     *
     * @param array $urls
     * @return array
     */
    public function filterUrls(array $urls):array
    {
        //todo
        return $urls;
    }


    /**
     * Replace http with https for known domains
     * May help to get httpstatus 200 instead of 301
     *
     * @param string $url
     * @return string
     */
    private function httpsFix(string $url):string
    {
        $list=[];
        // We know those host/providers use https
        // TODO make sure it works
        $list[]='youtube.com';
        $list[]='myspace.com';
        $list[]='soundcloud.com';
        $list[]='dailymotion.com';
        $list[]='imgur.com';
        $list[]='photobucket.com';
        $list[]='bandcamp.com';

        $x=parse_url($url);
        
        if (!$x) {
            return $url;
        }

        if (!isset($x['scheme'])) {
            return $url;
        }

        if ($x['scheme']=='https') {//https already ok
            return $url;
        }

        if (isset($x['host']) && in_array($x['host'], $list)) {
            //exit("$url http->https");
            return str_replace('http://','https://',$url);
        }
        return $url;
    }

    public function sanitize(string $url)
    {
        $url=trim($url);

        //Link doesnt start with `http` or `www`
        if(!preg_match("/^(http|www)/", $url)){
            return '';
        }

        // - http://benetbene.net/[/url
        if (preg_match("/\[.*/", $url)) {
            $url=preg_replace("/\[.*/", '', $url);
        }


        // url is too short to be valid
        if(strlen($url)<5)$url='';

        //remove trailing slash : http://benetbene.net/ -> http://benetbene.net
        $url=preg_replace("/\/$/",'', $url);

        //Skip Suspiciously long URL's
        if (strlen($url)>1020) {
            $url='';
        }

        return trim($url);
    }


    /**
     * Parse comments and extract links
     *
     * @return array
     */
    /*
     public function parseComments(array $records)
    {
        //We should extract bbcode tags first

        //minimal detection pattern
        $pattern = '/\bhttps?:\/\/\S+\b/';

        $reg_img='/\[img\](.*?)\[\/img\]/i';
        $reg_url='/\[url\](.*?)\[\/url\]/i';

        $links=[];
        foreach($records as $record){
            $urls=$this->extractUrls($record['Body']);
            if (count($urls)) {
                //print_r($urls);
                $links=array_merge($links,$urls);
            }
        }
        return $links;
    }
    */
}
