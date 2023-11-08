<?php

namespace App\Entity;

use App\Repository\DiscussionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscussionRepository::class)]
class Discussion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $discussion_id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $CreatedAt = null;

    //#[ORM\OneToMany(mappedBy: 'discussion', targetEntity: Link::class)]
    //private Collection $links;

    public function __construct()
    {
        $this->links = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): static
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    // /**
    //  * @return Collection<int, Link>
    //  */
    // public function getLinks(): Collection
    // {
    //     return $this->links;
    // }

    // public function addLink(Link $link): static
    // {
    //     if (!$this->links->contains($link)) {
    //         $this->links->add($link);
    //         $link->setDiscussion($this);
    //     }

    //     return $this;
    // }

    // public function removeLink(Link $link): static
    // {
    //     if ($this->links->removeElement($link)) {
    //         // set the owning side to null (unless already changed)
    //         if ($link->getDiscussion() === $this) {
    //             $link->setDiscussion(null);
    //         }
    //     }
    //     return $this;
    // }

}
