<?php

namespace App\Controller;

use App\Service\CartService;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private $entityManager;
    private $cartService;

    public function __construct(EntityManagerInterface $entityManager, CartService $cartService)
    {
        $this->entityManager = $entityManager;
        $this->cartService = $cartService;
    }

    /**
     * @Route("/mon-panier", name="cart")
     */
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $this->cartService->getFull()
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="add_to_cart")
     */
    public function add($id): Response
    {
        $this->cartService->add($id);

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/delete{id}", name="delete_to_cart")
     */
    public function delete($id): Response
    {
        $this->cartService->delete($id);
        
        return $this->redirectToRoute('cart');
    }
}
