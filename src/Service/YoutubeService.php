<?php

namespace App\Service;

//use App\Repository\DiscussionRepository;
//use App\Repository\LinkRepository;
use Exception;

class YoutubeService
{

    /**
     * From .ENV
     *
     * @var [type]
     */
    private $api_key;


    public function __construct()
    {
        $this->api_key=$_ENV['YOUTUBE_API_KEY'];
        if(!$this->api_key){
            throw new Exception("no YOUTUBE_API_KEY -> check .env", 1);
        }
    }



    /**
     * Get YT Video data from a given URL
     *
     * @param string $url
     * @return array
     */
    function fetchSnippet(string $url): array
    {

        if (!$this->api_key) {
            throw new Exception("No Youtube API Key",1);
        }

        $videoId = $this->url2key($url);

        if (!$videoId) {//key not found
            return [];
        }

        $apiUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=$videoId&key=" . $this->api_key;

        //echo "$apiUrl\n";
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);
        if ($data['items']&&count($data['items'])>0) { //Got VIDEO
            return $data['items'][0]['snippet'];
        }
        return [];
    }

    public function url2key(string $url):string|null
    {
        preg_match("#(?<=v=|v\/|vi=|vi\/|youtu.be\/)[a-zA-Z0-9_-]{11}#", $url, $match);
        if($match&&$match[0]){
            return $match[0];
        }
        return null;
    }


    /**
     * Return the best thumbnail found in array
     * (best -> highest size)
     * @param array $thumbnails
     * @return void
     */
    public function thumbnailUrl(array $thumbnails):string
    {
        $maxWidth = 0;
        $maxWidthUrl = '';
        foreach($thumbnails as $thumbnail){
            if ($thumbnail['width'] > $maxWidth) {
                $maxWidth = $thumbnail['width'];
                $maxWidthUrl = $thumbnail['url'];
            }
        }
        return $maxWidthUrl;
    }

}
