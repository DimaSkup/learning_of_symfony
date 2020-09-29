<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * Post constructor.
     */
    public function __construct()
    {
        //dd();
        //$this->setCreatedAt();
       //
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $titlе
     * @return Post
     */
    public function setTitle($title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     * @return Post
     */
    public function setBody($body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     * @return Post
     */
    public function setSlug($slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    /**
     * @param \DateTimeInterface $created_at
     * @return Post
     */
    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAtString(): string
    {
        return date("Y-m-d H:i:s", $this->created_at->getTimestamp());
    }

    /**
     * @return string|null
     */
    public function getBrochureFilename(): ?string
    {
        return $this->brochureFilename;
    }

    /**
     * @param string $brochureFilename
     * @return Post
     */
    public function setBrochureFilename($brochureFilename): self
    {
        $this->brochureFilename = $brochureFilename;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    /**
     * @param string $imageFilename
     * @return Post
     */
    public function setImageFilename($imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsModerated(): bool
    {
        return $this->isModerated;
    }

    /**
     * @param bool $isModerated
     * @return Post
     */
    public function setIsModerated(bool $isModerated): self
    {
        $this->isModerated = $isModerated;
        return $this;
    }


    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return (string)$this->user->getUsername();
    }

    /**
     * @param string $username
     * @return Post
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return (string )$this->user->getEmail();
    }

    /**
     * @param string $email
     * @return Post
     */
    public function setEmail($email): self
    {
        $this->userEmail = $email;
        return $this;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\Length(min=10, max=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $body;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $brochureFilename;

    /**
     * @var string|null
     * @Assert\Image
     * @ORM\Column(type="string", nullable=true)
     */
     private $imageFilename;

    /**
     * @var bool
     * @ORM\Column(name="is_moderated", type="boolean", nullable=false)
     */
    private $isModerated;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

}
