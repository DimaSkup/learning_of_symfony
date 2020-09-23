<?php

declare(strict_types=1);

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OAuthController extends AbstractController
{
    /**
     * @param ClientRegistry $clientRegistry
     * @return RedirectResponse
     * @Route("/connect/google", name="connect_google_start")
     */
    public function redirectToGoogleConnect(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'email', 'profile'
            ]);
    }

    /**
     * @Route("/google/auth", name="google_auth")
     * @return JsonResponse|RedirectResponse
     */
    public function connectGoogleCheck()
    {
        if (!$this->getUser())
            return new JsonResponse(['status' => false, 'message' => "User not found!"]);
        else
            return $this->redirectToRoute('blog_posts');
    }

    /**
     * @Route("connect/github", name="connect_github_start")
     *
     * @param ClientRegistry $clientRegistry
     *
     * @return RedirectResponse
     */
    public function redirectToGithubConnect(
        ClientRegistry $clientRegistry,
        Request $request
    )
    {
        $localeWhenSignInWithGithub = $request->query->get('curLocale');

        // save the user's current locale in a cookie
        $response = new Response('Content', Response::HTTP_OK, ['content-type' => 'text/html']);
        $response->headers->setCookie(new Cookie(
            'localeWhenSighInWithGithub',
            $localeWhenSignInWithGithub,
            strtotime('now + 30 days')
        ));
        $response->sendHeaders();

        return $clientRegistry
            ->getClient('github')
            ->redirect([
                'user', 'public_repo'
            ]);
    }

    /**
     * @Route("/github/auth", name="github_auth")
     *
     * @return RedirectResponse|Response
     */
    public function authenticateGithubUser()
    {
        if (!$this->getUser())
        {
            return new Response('User not found', 404);
        }

        return $this->redirectToRoute('blog_posts');
    }


    static public $curLocale;
}

?>