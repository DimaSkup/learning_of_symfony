<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GoogleUserProvider implements UserProviderInterface
{
    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $email
     *
     * @return mixed|UserInterface
     * @throws NonUniqueResultException
     */
    public function loadUserByUsername($username)
    {
        return $this->userRepository->loadUserByEmail($username);
    }

    /**
     * @param UserInterface $user
     * @return UserRepository
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof UserRepository)
        {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported. ', get_class($user))
            );
        }

        return $user;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return $class === 'App\Entity\OAuthGoogleUser';
    }

    /**
     * @var UserRepository
     */
    private $userRepository;
}

?>