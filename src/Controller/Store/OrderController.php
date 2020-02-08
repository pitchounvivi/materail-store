<?php

namespace App\Controller\Store;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/store/order", name="store_order")
     */
    public function index()
    {
        return $this->render('store/order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }
}
