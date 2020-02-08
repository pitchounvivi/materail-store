<?php

namespace App\Controller\Store;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/store/product")
 */
class ProductController extends AbstractController
{

    /**
     * @Route(
     *     "/create",
     *     name="store_product_create",
     *     methods={"GET","POST"}
     *     )
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        // Obtenir une instance du form
        $form = $this->createForm(
            ProductType::class,
            new Product()
        )->handleRequest($request);

        //si le form est submit et qu'il est valide
        if($form->isSubmitted() && $form->isValid()){
            //on récupère l'ORM
            $manager = $this->getDoctrine()->getManager();

            try {
                //cet ensemble envoie à la Database
                $manager->persist($form->getData());
                $manager->flush();

                //si tout va bien, on est redirigé/return vers show
                return $this->redirectToRoute("store_product_show_all");

            }catch (UniqueConstraintViolationException $exc){
                $form->addError(new FormError("Product already exists"));
            }
        }

        // Créer une Vue et passer la Vue au Template
        return $this->render('store/product/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     "/",
     *     name="store_product_show_all",
     *     methods={"GET"}
     *     )
     *
     * @param ProductRepository $repository
     * @return Response
     */
    public function showAll(ProductRepository $repository): Response
    {
        // Créer une Vue et passer la Vue au Template
        return $this->render('store/product/show_all.html.twig', [
            'product' => $repository->findAll(),
        ]);
    }

    /**
     * @Route(
     *     "/{id<\d{1,3}>}",
     *     name="store_product_show",
     *     methods={"GET"}
     *     )
     *
     * @param Product $product
     * @return Response
     */
    public function show(Product $product): Response
    {
        // Créer une Vue et passer la Vue au Template
        return $this->render('store/product/show.html.twig', [
            'produit' => $product
        ]);
    }

    /**
     * @Route(
     *     "/{id<\d{1,3}>}/edit",
     *     name="store_product_edit",
     *     methods={"GET","POST"}
     *     )
     *
     * @param Product $product
     * @param Request $request
     * @return Response
     */
    public function edit(Product $product, Request $request): Response
    {
        // Obtenir une instance du form
        $form = $this->createForm(
            ProductType::class,
            $product
        )->handleRequest($request);

        //si le form est submit et qu'il est valide
        if($form->isSubmitted() && $form->isValid()){
            //on récupère l'ORM
            $manager = $this->getDoctrine()->getManager();

            try {
                //cet ensemble envoie à la Database
                $manager->flush();

                //si tout va bien, on est redirigé/return vers show
                return $this->redirectToRoute("store_product_show",[
                    "id"=> $product->getId(),
                ]);

            }catch (UniqueConstraintViolationException $exc){
                $form->addError(new FormError("Product already exists"));
            }
        }

        return $this->render('store/product/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     "/{id<\d{1,3}>}/delete",
     *     name="store_product_delete",
     *     methods={"DELETE"}
     *     )
     *
     * @param Product $product
     * @param Request $request
     * @return Response
     */
    public function delete(Product $product, Request $request): Response
    {
        $token =$request->request->get('_token');

        if ($this->isCsrfTokenValid(
            "delete".$product->getId(),
            $token
        )){
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($product);
            $manager->flush();
        }

        return $this->redirectToRoute("store_product_show_all");
    }
}
