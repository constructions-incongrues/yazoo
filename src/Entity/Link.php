<?php

namespace App\Entity;

//use App\Entity\Discussion;
use App\Repository\LinkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1023)]
    private ?string $url = null;



    #[ORM\Column(nullable: true)]
    private ?int $discussion_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $comment_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $contributor_id = null;


    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $status = null;


    #[ORM\Column(length: 31, nullable: true)]
    private ?string $mimetype = null;

    #[ORM\Column(nullable: true)]
    private ?array $data = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 1023, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $provider = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $canonical = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $visited_at = null;

    //#[ORM\ManyToOne(inversedBy: 'links')]
    //private ?discussion $discussion = null;


    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUrl(): ?string
    {

        if (preg_match("/^www/",$this->url)) {
            return 'http://'.$this->url;
        }

        return $this->url;
    }

    public function getHost(): ?string
    {
        $parsed=parse_url($this->getUrl());
        if (!empty($parsed['host'])) {
            return $parsed['host'];
        }
    }

    public function getFilename(): ?string
    {
        $parsed=parse_url($this->getUrl());
        if (!empty($parsed['path'])) {
            return basename($parsed['path']);
        }
        return '';
    }


    public function getExtension(): ?string
    {
        $parsed=parse_url($this->getUrl());
        if(!empty($parsed['path'])){
            $x=explode('.',$parsed['path']);
            return end($x);
        }

        return '';
    }

    public function setUrl(string $url): static
    {
        if (preg_match("/^www/",$url)) {
            $url='http://'.$url;
        }

        $this->url = $url;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDiscussionId(): ?int
    {
        return $this->discussion_id;
    }

    public function setDiscussionId(int $discussion_id): static
    {
        $this->discussion_id = $discussion_id;

        return $this;
    }

    public function getCommentId(): ?int
    {
        return $this->comment_id;
    }

    public function setCommentId(int $comment_id): static
    {
        $this->comment_id = $comment_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getContributorId(): ?int
    {
        return $this->contributor_id;
    }

    public function setContributorId(?int $contributor_id): static
    {
        $this->contributor_id = $contributor_id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        if ($title) {
            $title = mb_substr($title, 0, 255, 'UTF-8');
        }
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        if ($description) {
            $description=substr($description,0,1023);//max db length
        }
        $this->description = $description;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    // Sanitize the input string to remove unsupported characters
    private function sanitizeString($input) {
        // Remove characters that are not supported by your database
        $sanitizedString = preg_replace('/[^\p{L}\p{N}\s]/u', '', $input);
        //$sanitizedString = preg_replace('/[^\p{L}\p{N}\s]/u', '', $input);

        // Ensure the string length does not exceed the column's length
        $maxLength = 255; // Adjust the maximum length according to your column definition
        $sanitizedString = mb_substr($sanitizedString, 0, $maxLength, 'UTF-8');

        return $sanitizedString;
    }


    function ensureUTF8Encoding($string) {//MEH
        // Detect the current encoding of the string
        $encoding = mb_detect_encoding($string, mb_detect_order(), true);

        // If the encoding is not UTF-8, convert the string to UTF-8
        if ($encoding !== 'UTF-8') {
            $string = mb_convert_encoding($string, 'UTF-8', $encoding);
        }

        return $string;
    }

    /**
     * Return html code for a preview of the link, when possible
     *
     * @return void
     */
    /*
    public function getPreview():string
    {
        $url=$this->url;
        $parsed=parse_url($url);
        $filename=basename($url);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $htm='no preview';
        //$htm.=print_r($parsed,true);



        //Image Preview

        //Youtube Preview
        //Video Preview
        return $htm;
    }
    */

    public function getCanonical(): ?string
    {
        return $this->canonical;
    }

    public function setCanonical(?string $canonical): static
    {
        $this->canonical = mb_substr($canonical, 0, 255, 'UTF-8');
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        if (strlen($image)>255) {
            $image='';//fuck it
        }

        $this->image = $image;

        return $this;
    }

    public function getMimetype(): ?string
    {
        return $this->mimetype;
    }

    public function setMimetype(?string $mimetype): static
    {
        $this->mimetype = $mimetype;

        return $this;
    }
    /*
    public function getDiscussion(): ?discussion
    {
        return $this->discussion;
    }

    public function setDiscussion(?discussion $discussion): static
    {
        $this->discussion = $discussion;

        return $this;
    }
    */

    public function getVisitedAt(): ?\DateTimeImmutable
    {
        return $this->visited_at;
    }

    public function setVisitedAt(?\DateTimeImmutable $visited_at): static
    {
        $this->visited_at = $visited_at;

        return $this;
    }

    /**
     * Set visited_at to 'NOW'
     *
     * @return void
     */
    public function visited(): static
    {
        $this->visited_at = new \DateTimeImmutable();
        return $this;
    }

}
