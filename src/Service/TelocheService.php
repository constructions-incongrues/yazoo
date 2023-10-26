<?php

namespace App\Service;

use Exception;

class TelocheService
{


    //https://docs.joinpeertube.org/api-rest-reference.html#section/Rate-limits


    /**
     * From .ENV
     *
     * @var [type]
     */
    private $URL=null;
    private $username=null;
    private $password=null;
    private $access_token=null;
    private $refresh_token=null;


    public function __construct()
    {
        $this->URL=$_ENV['PEERTUBE_URL'];
        $this->username=$_ENV['PEERTUBE_USERNAME'];
        $this->password=$_ENV['PEERTUBE_PASSWORD'];
        if (!$this->api_key) {
            throw new Exception("no PEERTUBE_URL -> check .env", 1);
       }
    }

    public function authenticate()
    {
        $dat=$this->getTokens();
        $this->userToken($dat['client_id'],$dat['client_secret']);
    }

    public function getTokens()
    {
        $endpoint=$this->URL.'/api/v1/oauth-clients/local';

        $response = file_get_contents($endpoint);
        $data = json_decode($response, true);

        if ($data) {
            return $data;
        }

        return [];
    }

    public function userToken(string $client_id, string $client_secret)
    {

        //https://docs.joinpeertube.org/api/rest-getting-started

        // API endpoint
        $endpoint = $this->URL."/api/v1/users/token";

        echo "endpoint=$endpoint\n";

        // Request parameters
        $data = array(
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "grant_type" => "password",
            "username" => $this->username,
            "password" => $this->password
        );

        // Initialize cURL session
        $ch = curl_init($endpoint);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // Execute cURL session and store the API response
        $response = curl_exec($ch);

        // Check for cURL errors
        if(curl_error($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Handle the API response
        if ($response === FALSE) {
            echo "Error occurred while making the API request.";
        } else {
            $jsonResponse = json_decode($response, true);
            $this->access_token=$jsonResponse['access_token'];
            $this->refresh_token=$jsonResponse['refresh_token'];
            return $jsonResponse;
        }

        return [];
    }


    public function listVideoChannels(): array
    {
        //echo __FUNCTION__."()\n";

        //https://docs.joinpeertube.org/api-rest-reference.html#tag/Video-Channels
        $endpoint = $this->URL."/api/v1/video-channels";
        //echo "$endpoint\n";

        //$ch = curl_init($endpoint);// Initialize cURL session        //echo "$endpoint\n";
        //$response = curl_exec($ch);// Execute cURL session and store the API response
        //curl_close($ch);// Close cURL session

        $response = file_get_contents($endpoint);
        $data = json_decode($response, true);
        return $data;
        // Handle the API response
        /*
        if ($response === FALSE) {
            throw new Exception("no response", 1);
        } else {
            $jsonResponse = json_decode($response, true);
            return $jsonResponse;
        }
        */
        return [];
    }

    public function listVideos(): array
    {
        //TODO
        $user='yazoo';
        $endpoint = $this->URL."/api/v1/$user/videos";

        return [];
    }

    public function importVideo(string $videoUrl, int $channel_id)
    {


        echo "importVideo(string $videoUrl)\n";

        // API endpoint for video import
        $url = $this->URL."/api/v1/videos/imports";

        // Video URL to be imported
        //$videoUrl = "https://www.youtube.com/embed/UnTAJL1Eack";

        // Request parameters
        $data = array(
            "channelId" => $channel_id,//yazoo main channel;
            //"name" => "Test Video Name",
            "targetUrl" => $videoUrl,
            "category" => 1,//music
            "privacy" => 1,//public
        );

        if (!$this->access_token) {
            throw new Exception("no access token. auth first", 1);
        }

        // Access token received after authentication
        $accessToken = $this->access_token;

        // Initialize cURL session
        $ch = curl_init($url);
        echo "$url\n";

        // Set cURL options for POST request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // Set Authorization header with Bearer token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $accessToken
        ));

        // Execute cURL session and store the API response
        $response = curl_exec($ch);

        echo "response:$response\n";

        // Check for cURL errors
        if(curl_error($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }else{
            echo "No Curl error\n";
        }

        // Close cURL session
        curl_close($ch);

        // Handle the API response
        if ($response === FALSE) {
            echo "Error occurred while importing the video.\n";
        } else if (!$response){
            echo "No response\n";
        } else {
            $jsonResponse = json_decode($response, true);
            // Handle the JSON response as needed
            print_r($jsonResponse);
            return $jsonResponse;
        }
        return [];
    }

}
