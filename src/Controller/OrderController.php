<?php

namespace App\Controller;

use App\Service\CartService;
use App\Service\MailService;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $entityManager;
    private $mailService;

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
    }

    /**
     * @Route("/commande", name="order")
     */
    public function index(CartService $cartService, Request $request): Response
    {
            $date = new \DateTime();

            $order = new Order();
            $order->setUser($this->getUser());
            foreach ($cartService->getFull() as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setProduct($product);
                $order->addOrderDetail($orderDetails);
            }

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $cartService->remove();

            // Envois un email a la messagerie trueclothesparis avec un récap de la commande
            $this->mailService->sendEmail(
                'trueclothesparis@gmail.com',
                $order->getUser()->getFirstname(),
                'Récapitulatif de la demande N° '.$order->getId(),
                'email/cart_success_email.html.twig',
                [
                    'data' => "Commande N° ". $order->getId()."<hr/> Adresse mail du client : " .$order->getUser()->getEmail() . "<hr/> Nom du client " .$order->getUser()->getFullName(),
                    'orders' =>$order->getOrderDetails()->toArray(),
                ]
            );
            
            // Envois un email au client avec le recap de sa commande
            $this->mailService->sendEmail(
                $order->getUser()->getEmail(),
                $order->getUser()->getFirstname(),
                'Votre demande au sein de True Clothes Paris est bien validé. Nous vous recontacterons',
                'email/cart_success_email.html.twig',
                [
                    'data' => "Bonjour " . $order->getUser()->getFullName() ." <hr/> Réference de la commande° ". $order->getId(). " <br/> Merci pour votre demande nous vous recontacterons dans les plus brefs delais",
                    'orders' =>$order->getOrderDetails()->toArray(),
                ]
            );

            return $this->render('order/add.html.twig', [
                'order' => $order,
                'reference' => $order->getId(),
            ]);
    }
}
