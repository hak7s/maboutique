<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\MailService;
use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    private $entityManager;
    private $mailService;
    
    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
    }
    /**
     * @Route("/nous-contacter", name="contact")
     */
    public function index(Request $request): Response
    {
        $contact = new Contact();
        $form=$this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();
            


            $this->mailService->sendEmail(
                'trueclothesparis@gmail.com',
                $contact->getNom().' '.$contact->getPrenom(),
                'Nouveau contact',
                'email/contact_success_email.html.twig',
                [
                    'data' => $contact->getContent(),
                ]
            );

            $this->mailService->sendEmail(
                $contact->getEmail(),
                $contact->getNom().' '.$contact->getPrenom(),
                'Nouveau contact',
                'email/contact_success_email.html.twig',
                [
                    'data' => $contact->getContent(),
                ]
            );
            $this->addFlash('notice','Merci de nous avoir contacté. Notre équipe va vous répondre dans les meilleurs délais');
	    return $this->redirectToRoute('contact');
           
        }
        return $this->render('contact/index.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
