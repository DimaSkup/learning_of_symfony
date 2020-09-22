<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAUth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;

use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class OAuthGoogleAuthenticator extends SocialAuthenticator
{
    /**
     * @param ClientRegistry $clientRegistry
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepository
     */
    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        UserRepository $userRepository
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    // Викликається, коли користувачу необхідна авторизація,
    // при запиті до заборонених ресурсів.
    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     *
     * @return RedirectResponse|Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/',
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    // Викликається на кожен запит, щоб вирішити чи цей аутентифікатор
    // повинен виконуватись на даного запиту. Повертає "false", якщо цей
    // аутентифікатор повинен бути пропущений
    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'google_auth';
    }

    // Викликається, якщо цей аутентифікатор повинен бути виконаний.
    // Повертає дані, які ви хочете передати до функції getUser(), в якості прав.
    /**
     * @param Request $request
     * @return AccessToken|mixed
     */
    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return User|null|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($credentials);

        $email = $googleUser->getEmail();
        $clientId = $googleUser->getId();

        /** @var User $existingUser */
        $existingUser = $this->userRepository
            ->findOneBy(['clientId' => $clientId]);

        if ($existingUser)
        {
            return $existingUser;
        }

        /** @var User $user */
        $user = $this->userRepository
            ->findOneBy(['email' => $email]);

        if ($user)
        {
            $user->setClientId($clientId);
            return $user;
        }
        elseif (!$user)
        {
            $user = User::fromGoogleRequest(
                $clientId,
                $email,
                $googleUser->getName()
            );

            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return null|Response|void
     */
    public function onAUthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): ?Response
    {
        echo "MDA<br/>";
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     *
     * @return null|Response
     */
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $providerKey
    ): ?Response
    {
        return null;
    }

    /**
     * @return OAuth2Client
     */
    public function getGoogleClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('google');
    }

    /**
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return true;
    }

    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var userRepository
     */
    private $userRepository;
}

?>