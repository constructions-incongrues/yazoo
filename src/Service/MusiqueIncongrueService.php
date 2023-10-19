<?php

namespace App\Service;

use App\Repository\DiscussionRepository;
use App\Repository\LinkRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Exception;

class MusiqueIncongrueService
{

    /**
     * From .ENV
     *
     * @var [type]
     */
    private $email;
    private $password;//from .ENV
    private $token=null;

    private $linkRepository;
    private $discussionRepository;

    public function __construct(LinkRepository $linkRepository, DiscussionRepository $discussionRepository)
    {
        $this->linkRepository=$linkRepository;
        $this->discussionRepository=$discussionRepository;
        $this->email=$_ENV['DIRECTUS_EMAIL'];
        $this->password=$_ENV['DIRECTUS_PASS'];
    }

    /**
     * Authenticate and return TOKEN
     *
     * @return string TOKEN
     */
    public function authenticate():string
    {

        $endpoint = "https://data.constructions-incongrues.net/musiques-incongrues/auth/authenticate";

        // Data to be sent in the POST request
        $data = array(
            'email' => $this->email,
            'password' => $this->password
        );
        //var_dump($data);exit;

        $ch = curl_init($endpoint);

        // Set cURL options for authentication and request
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //TODO add a max exec time here


        $response = curl_exec($ch);
        curl_close($ch);

        $data=json_decode($response, true);
        //var_dump($data);exit;
        if ($data['data']['token']) {
            $this->token=$data['data']['token'];
        }else{
            throw new Exception("Error Retreiving token", 1);
        }

        return $this->token;
    }

    public function fetch(string $endpoint)
    {

        if (!$this->token) {
            $this->authenticate();
            //throw new Exception("no token", 1);
        }

        $ch = curl_init($endpoint);
        // Set cURL options for GET request with Authorization header
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Return new MI comments since Yazoo last inserted link.
     * note: As a consequence, if new comments do not contain links, the lastCommentId wont be updated.
     * we could use the FilesystemAdapter instead.
     * @param string $token
     * @return array
     */
    public function fetchComments(): array
    {

        if (!$this->token) {
            $this->authenticate();
        }

        if (!$this->token) {
            throw new Exception("no token", 1);
        }

        $lastCommentID=$this->linkRepository->findHighestCommentId();
        $endpoint = "https://data.constructions-incongrues.net/musiques-incongrues/items/LUM_Comment";
        $endpoint.="?sort=" . urlencode("CommentID");
        $endpoint.="&filter[CommentID][gt]=$lastCommentID";
        return $this->fetch($endpoint);
    }

    /**
     * Fetch some discussions
     *
     * @return array
     */
    public function fetchDiscussions(int $limit=100): array
    {
        $this->authenticate();

        if (!$this->token) {
            throw new Exception("no token", 1);
        }


        $lastDiscussion=$this->discussionRepository->findByHighestId();
        if ($lastDiscussion) {

            $lastDiscussionID=$lastDiscussion->getDiscussionId();
            //var_dump($lastDiscussionID);exit;
        }else{
            $lastDiscussionID=0;
        }
        //var_dump($lastDiscussionID);exit;

        $endpoint = "https://data.constructions-incongrues.net/musiques-incongrues/items/LUM_Discussion?";
        $endpoint.="&fields=DiscussionID,Name,DateCreated";
        $endpoint.="&limit=$limit";
        $endpoint.="&sort=" . urlencode("DiscussionID");
        $endpoint.="&filter[DiscussionID][gt]=$lastDiscussionID";
        $data= $this->fetch($endpoint);
        //dd($data);exit;
        foreach($data['data'] as $k=>$r){
            $Name=$r['Name'];

            //latin1_swedish_ci to UTF8 Manual encoding fix
            $Name=str_replace('Ã ','à',$Name);//a grave
            $Name=str_replace('Ã©','é',$Name);
            $Name=str_replace('Ã¨','è',$Name);//e accent grave
            $Name=str_replace('Ãª','ê',$Name);//e accent circonflexe
            $Name=str_replace('Ã§','ç',$Name);//cedille
            $Name=str_replace('Ã¯','ï',$Name);//i trema
            $Name=str_replace('Ã´','ô',$Name);//

            $data['data'][$k]['Name']=$Name;
        }
        return $data;
    }

}
