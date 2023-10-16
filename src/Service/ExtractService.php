<?php

namespace App\Service;

class ExtractService
{
    
    
    /**
     * Extract a list of url strings from a given text
     * Focus on bbcode URL's first, and match stray urls last
     * @param string $text
     * @return array
     */
    public function extractUrls(string $text)
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
            //echo "<li>match=$match";
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
            $urls[$k]=$this->sanitize($url);
        }

        return array_unique($urls);
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
            
        // - http://*
        //this is invalid   
        
        // - AND
        if(strlen($url)<5)$url='';

        //remove trailing slashe ( http://benetbene.net/ -> http://benetbene.net )
        $url=preg_replace("/\/$/",'', $url);

        //Extra long URL's
        if(strlen($url)>1020){
            echo "Suspiciously long URL:\n$url\n";
            $url='';
            //exit($url);
        }

        return trim($url);
    }
    
    
    /**
     * Parse comments and extract links
     *
     * @return array
     */
    
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
            
            
            /*
            $body=$record['Body'];
            
            preg_match_all($pattern,$body, $o);
            
            //[img]http://i69.photobucket.com/albums/i57/taueber/f_farNnightwem_2885629.jpg[/img]
            
            foreach($o[0] as $url){
                
                preg_match_all($reg_img, $body, $img);
                foreach($img[0] as $urlimg){
                    echo "<li>[IMG]=$urlimg";
                }


                preg_match_all($reg_url, $body, $urls);
                foreach($urls[0] as $urlurl){
                    echo "<li>[URL]=$urlurl";
                }
                
                echo "<li>$url\n";
                
            }
            */
            
            /*
            if (count($o[0])) {
                $link=[];
                $link['urls']=$o[0];
                $link['CommentID']=$record['CommentID'];
                $link['DiscussionID']=$record['DiscussionID'];
                $link['AuthUserID']=$record['AuthUserID'];                
                $links[]=$link;
                error_log($o[0][0], 3, "/tmp/links.log");
            }
            */
        }
        return $links;
    }

 

}
