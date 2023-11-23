<?php

namespace App\Service;

use App\Repository\DiscussionRepository;
use App\Repository\LinkRepository;
use Exception;

class MusiqueIncongrueService
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

    public function __construct(LinkRepository $linkRepository, DiscussionRepository $discussionRepository)
    {
        $this->linkRepository=$linkRepository;
        $this->discussionRepository=$discussionRepository;

        if (isset($_ENV['DIRECTUS_EMAIL'])) {
            $this->directus_email=$_ENV['DIRECTUS_EMAIL'];
        }

        if (isset($_ENV['DIRECTUS_PASSWORD'])) {
            $this->directus_password=$_ENV['DIRECTUS_PASSWORD'];
        }
    }

    /**
     * Authenticate and return Directus API TOKEN
     *
     * @return string TOKEN
     */
    public function authenticate():string
    {
        $endpoint = "https://data.constructions-incongrues.net/musiques-incongrues/auth/authenticate";

        if (!isset($this->directus_email)) {
            throw new Exception("no directus_email. check env", 1);
        }

        if (!isset($this->directus_password)) {
            throw new Exception("no directus_password. check env", 1);
        }

        // Data to be sent in the POST request
        $data = array(
            'email' => $this->directus_email,
            'password' => $this->directus_password
        );

        $ch = curl_init($endpoint);
        // Set cURL options for authentication and request
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //TODO add a max exec time here

        $response = curl_exec($ch);
        curl_close($ch);

        $data=json_decode($response, true);
        
        if (isset($data['data']['token'])) {
            $this->token=$data['data']['token'];
        }else{
            throw new Exception("Error Retrieving token", 1);
        }
        return $this->token;
    }

    /**
     * Request endpoint, return content as Associative array
     */
    private function fetch(string $endpoint): array
    {

        if (!$this->token) {
            $this->authenticate();
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
            throw new Exception("no auth token", 1);
        }

        $lastDiscussion=$this->discussionRepository->findByHighestId();
        $lastDiscussionID=0;
        if ($lastDiscussion) {
            $lastDiscussionID=$lastDiscussion->getDiscussionId();            
        }

        $endpoint ="https://data.constructions-incongrues.net/musiques-incongrues/items/LUM_Discussion?";
        $endpoint.="&fields=DiscussionID,Name,DateCreated";
        $endpoint.="&limit=$limit";
        $endpoint.="&sort=" . urlencode("DiscussionID");
        $endpoint.="&filter[DiscussionID][gt]=$lastDiscussionID";
        $data= $this->fetch($endpoint);

        foreach($data['data'] as $k=>$r){
            $data['data'][$k]['Name']=$this->fixLatinGarbageEncoding($r['Name']);
        }
        return $data;
    }


    /**
     * latin1_swedish_ci garbled to UTF8, Manual but SAFE encoding fix
     *
     * @param string $garbage
     * @return string
     */
    private function fixLatinGarbageEncoding(string $garbage): string
    {
        $str=$garbage;
        //latin1_swedish_ci to UTF8 Manual but SAFE encoding fix
        $str=str_replace('Ã ', 'à', $str);//a grave (TODO marche pas)
        $str=str_replace('Ã©', 'é', $str);
        $str=str_replace('Ã‰', 'É', $str);//E aigu Maj
        $str=str_replace('Ã¨', 'è', $str);//e accent grave
        $str=str_replace('Ãª', 'ê', $str);//e accent circonflexe
        $str=str_replace('Ã§', 'ç', $str);//cedille
        $str=str_replace('Ã¯', 'ï', $str);//i trema
        $str=str_replace('Ã´', 'ô', $str);//
        $str=str_replace('Å“', 'œ', $str);//œ

        $str=str_replace('â€™', "'",$str);//apostrophe

        return $str;
    }

}
