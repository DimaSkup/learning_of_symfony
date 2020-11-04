<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Event\RegisteredUserEvent;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\CodeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function register(
        UserPasswordEncoderInterface $passwordEncoder,
        Request $request,
        CodeGenerator $codeGenerator,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();

            $password = $passwordEncoder->encodePassword(
                $user,
                $user->getPlainPassword()
            );
            $user->setPassword($password);
            $user->eraseCredentials();
            $user->setConfirmationCode($codeGenerator->getConfirmationCode());
            $user->setEnabled(false);

            $em = $this->getDoctrine()->getManager();

            $em->persist($user);
            $em->flush();

            //$mailer->sendConfirmationMessage($user);
            $userRegisteredEvent = new RegisteredUserEvent($user);
            $eventDispatcher->dispatch($userRegisteredEvent, RegisteredUserEvent::NAME);
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/confirm/{code}", name="email_confirmation")
     */
    public function confirmEmail(UserRepository $userRepository, string $code)
    {
        /** @var User $user */
        $user = $userRepository->findOneBy(['confirmationCode' => $code]);

        $em = $this->getDoctrine()->getManager();

        if ($user === null)
        {
            return new Response('404');
        }

        $user->setEnabled(true);
        $user->setConfirmationCode("");

        $em->flush();

        return $this->render('security/account_confirm.html.twig', [
            'user' => $user,
        ]);
    }
}
