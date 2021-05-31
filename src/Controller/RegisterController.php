<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private $entityManager;
    private $mailService;

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
    }

    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->mailService->sendEmail(
                $user->getEmail(),
                $user->getFirstname(),
                'Bienvenue sur True Clothes Paris',
                'email/register.html.twig',
                [
                    'user' => $user,
                    'url' => $this->generateUrl('home', [], UrlGeneratorInterface::ABSOLUTE_PATH)
                ]
            );

            $notification = "Votre inscription s'est correctement déroulée .Vous pouvez dés a présent vous connecter à votre compte.";
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
