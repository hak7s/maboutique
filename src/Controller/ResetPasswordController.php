<?php

namespace App\Controller;

use DateTime;
use App\Service\MailService;
use App\Entity\User;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordController extends AbstractController
{
    private $entityManager;
    private $mailService;

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
    }
    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));

            if ($user) {

                //1 : Enregistrer en base la demande de reset_password avec user , token , creatdAT
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new DateTime());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();
                

                //2 : Envoyer un email a lutilisateur avec un lien lui permettant de mettre a jour son mot de passe.
                $url = $this->generateUrl('update_password', [
                    'token' => $reset_password->getToken()
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $content = "Bonjour" . $user->getFirstname() . "<br/>Vous avez demandé a réinitialiser  votre mot de passer sur le site trueclotheparis.<br/><br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='" . $url . "'> mettre à jour votre mot de passse </a>. ";
                
                $this->mailService->sendEmail(
                    $user->getEmail(),
                    $user->getFirstname(). ' ' . $user->getLastname(),
                    'Réinitialiser votre mot de passe sur trueclotheparis',
                    'email/contact_success_email.html.twig',
                    [
                        'data'=>$content,
                    ]
                );

                $this->addFlash('notice', 'Vous allez recevoir dans un instant un mail avec la procedur pour réinitialiser votre mot de passe.');
            } else {
                $this->addFlash('notice', 'Cette adresse email est inconnue.');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    /**
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update(Request $request, $token, UserPasswordEncoderInterface $encoder): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$reset_password) {
            return $this->redirectToRoute('reset_password');
        }

        //Verifier si le createdAT = now - 3h
        $now = new DateTime();
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {
            $this->addFlash('notice', 'Votre demande de mot de passe a expiré. Merci de la renouveller');
            return $this->redirectToRoute('reset_password');
        }
        //Rendre une vue avec mot de passe et confirmer votre mot de passe 
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new_pwd = $form->get('new_password')->getData();
            //Encodage des mots de passe 
            $password = $encoder->encodePassword($reset_password->getUser(), $new_pwd);

            $reset_password->getUser()->setPassword($password);

            //Flush en base de donnéé.
            $this->entityManager->flush();

            //Redirection de l'utilisateur vers la page de connexion
            $this->addFlash('notice', 'Votre mot de passe a bien été mis a jour.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
