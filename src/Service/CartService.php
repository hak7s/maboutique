<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private $session;
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    public function add($id)
    {
        $cart = $this->session->get('cart', []);

        if(false === isset($cart[$id])){
            $cart[$id]= 1;
        }

        $this->session->set('cart', $cart);
    }
    public function get()
    {
        return $this->session->get('cart', []) ;
    }

    public function remove()
    {
        return $this->session->remove('cart') ;
    }

    public function delete($id)
    {
        $cart = $this->session->get('cart', []);
        
        if(true === isset($cart[$id])){
            unset($cart[$id]);
        }

        return $this->session->set('cart', $cart) ;
    }

    public function getFull()
    {
        $cartComplete =[];

        foreach ($this->get() as $id => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($id);

            if(null === $product){
                $this->delete($id);
                continue;
            }

            $cartComplete[] = $product;
        }

        return $cartComplete;
    }
}