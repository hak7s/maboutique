<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class MailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail($to, $name, $subject, $template, $templateParams = []) 
    {
        $email = (new TemplatedEmail())
            ->from('trueclothesparis@gmail.com')
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($templateParams);

        $email->getHeaders()->addHeader('X-MJ-TemplateID', 2717244);

        $this->mailer->send($email);
    }
}