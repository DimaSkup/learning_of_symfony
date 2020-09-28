<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="user")
 * @UniqueEntity(fields={"email"}, message="You already have an account")
 */
class User implements UserInterface
{

    public const GITHUB_OAUTH = 'Github';
    public const GOOGLE_OAUTH = 'Google';

    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';


    public function __construct()
    {
        $this->setRoles([self::ROLE_USER]);
        $this->setLastLoginTime(new DateTime('now'));
        $this->posts = new ArrayCollection();

        // if any parameters are transferred to the constructor, we call the function
        // which will process them, differently we continue work of the constructor
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f='__construct'.$i))  // the case of calling the constructor with a certain number of parameters
        {
            call_user_func_array(array($this, $f), $a);
        }
        else        // the case of calling the constructor without parameters
        {
            $this->setOauthType('legasy');
            $this->setEnabled(false);
        }
        //$this->comments = new ArrayCollection();
    }

    /**
     * Another constructor for OAuth Authentication
     *
     * @param $clientId
     * @param string $email
     * @param string $username
     * @param string $oauthType
     * @param array $roles
     */
    public function __construct5(
        $clientId,
        string $email,
        string $username,
        string $oauthType,
        array $roles
    )
    {
        $this->setEnabled(true);
        $this->setClientId($clientId);
        $this->setEmail($email);
        $this->setUsername($username);
        $this->setOauthType($oauthType);
    }

    /**
     * @param int $clientId
     * @param string $email
     * @param string $username
     *
     * @return User
     */
    public static function fromGithubRequest(
        int $clientId,
        string $email,
        string $username
    ): User
    {
        return new self(
            $clientId,
            $email,
            $username,
            self::GITHUB_OAUTH,
            [self::ROLE_USER]
        );
    }

    /**
     * @param string $clientId
     * @param string $email
     * @param string $username
     *
     * @return User
     */
    public static function fromGoogleRequest(
        int $clientId,
        string $email,
        string $username
    )
    {
        return new self(
            $clientId,
            $email,
            $username,
            self::GOOGLE_OAUTH,
            [self::ROLE_USER]
        );
    }


    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): string
    {
        return (string) $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return $this
     */
    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }



    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
         $this->plainPassword = null;
    }

    /**
     * @return string
     */
    public function getConfirmationCode(): string
    {
        return $this->confirmationCode;
    }

    /**
     * @param string $confirmationCode
     * @return User
     */
    public function setConfirmationCode(string $confirmationCode): self
    {
        $this->confirmationCode = $confirmationCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return User
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getLastLoginTime(): DateTimeInterface
    {
        return $this->lastLoginTime;
    }

    /**
     * @param DateTimeInterface $lastLoginTime
     * @return User
     */
    public function setLastLoginTime(DateTimeInterface $lastLoginTime): self
    {
        $this->lastLoginTime = $lastLoginTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getOAuthType(): string
    {
        return $this->oauthType;
    }

    /**
     * @param string $oauthType
     * @return $this
     */
    public function setOAuthType(string $oauthType): self
    {
        $this->oauthType = $oauthType;
        return $this;
    }

    /**
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @param int $clientId
     * @return $this
     */
    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=180)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $plainPassword;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $confirmationCode;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private $lastLoginTime;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $oauthType;

    /**
     * @var int
     * @ORM\Column(type="string", nullable=true)
     */
    private $clientId;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="user")
     */
    private $posts;

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }
}
