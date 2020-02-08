<?php

namespace App\Controller\Store;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\Form\FlusherService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/store/category")
 */
class CategoryController extends AbstractController
{


    /**
     * @Route(
     *     "/new",
     *     name="store_category_new",
     *     methods={"GET","POST"}
     *     )
     *
     * @param Request $request
     * @param CacheItemPoolInterface $pool
     * @param CategoryRepository $repository
     * @param FlusherService $flusherService
     * @return Response
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function new(
        Request $request,
        CacheItemPoolInterface $pool,
        CategoryRepository $repository,
        FlusherService $flusherService): Response
    {

        // Obtenir une instance du form
        $form = $this->createForm(
            CategoryType::class,
            new Category()
        )->handleRequest($request);


        if ($flusherService->flush($form, "Category already exists", true)) {

            //Mise en place du cache
            $key = "categories";
            $item = $pool->getItem($key);
            $categories = $item->get();

            if (!$categories) {
                $categories = $repository->findAll();
            } else {
                $categories[] = $form->getData();
            }

            $item->set($categories);
            $pool->save($item);
            //fin mise en cache

            return $this->redirectToRoute("store_category_show_all");
        }

        // Créer une Vue et passer la Vue au Template
        return $this->render('store/category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     "/",
     *     name="store_category_show_all",
     *     methods={"GET"}
     *     )
     *
     * @param CategoryRepository $repository
     * @param CacheItemPoolInterface $pool
     * @return Response
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function showAll(
        CategoryRepository $repository,
        CacheItemPoolInterface $pool): Response
    {
        //mise en cache
        $key = "categories";
        $item = $pool->getItem($key);

        if ($item->isHit()) {
            $item->set($repository->findAll());
            $pool->save($item);
        }
        //fin mise en cache

        //pour l'affichage
        return $this->render('store/category/show_all.html.twig', [
            'categories' => $repository->findAll(),
        ]);
    }

    /**
     * @Route(
     *     "/{id<\d{1,3}>}",
     *     name="store_category_show",
     *     methods={"GET"}
     *     )
     *
     * @param Category $category
     * @return Response
     */
    public function show(Category $category): Response
    {
        // avec le paramètre Category, on déclare juste notre entity et symfony
        // gère l'affichage d'une mauvaise adresse
        return $this->render('store/category/show.html.twig', [
            'category' => $category
        ]);
    }

    /**
     * @Route(
     *     "/{id<\d{1,3}>}/edit",
     *     name="store_category_edit",
     *     methods={"GET","POST"}
     *     )
     *
     * @param Category $category
     * @param Request $request
     * @param CategoryRepository $repository
     * @param CacheItemPoolInterface $pool
     * @return Response
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function edit(
        Category $category,
        Request $request,
        CategoryRepository $repository,
        CacheItemPoolInterface $pool): Response
    {
        // Obtenir une instance du form
        $form = $this->createForm(
            CategoryType::class,
            $category
        )->handleRequest($request);

        //si le form est submit et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {
            //on récupère l'ORM
            $manager = $this->getDoctrine()->getManager();

            try {
                //cet ensemble envoie à la Database
                $manager->flush();

                //Mise en cache
                $key = "categories";
                $item = $pool->getItem($key);

                $categories = $item->get();

                //Trouver dans le cache l'item édité
                foreach ($categories as $key => $value){
                    if ($value->getId() === $category->getId()){
                        //Remplacer l'entité en cache par cette entitée
                        $categories[$key] = $category;
                        break;
                    }
                }
                $item->set($categories);
                $pool->save($item);
                //Fin mise en cache

                //si tout va bien, on est redirigé/return vers show
                return $this->redirectToRoute("store_category_show", [
                    "id" => $category->getId(),
                ]);

            } catch (UniqueConstraintViolationException $exc) {
                $form->addError(new FormError("Category already exists"));
            }
        }

        return $this->render('store/category/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     "/{id<\d{1,3}>}/delete",
     *     name="store_category_delete",
     *     methods={"DELETE"}
     *     )
     *
     * @param Category $category
     * @param Request $request
     * @return Response
     */
    public function delete(Category $category, Request $request): Response
    {
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid(
            "delete" . $category->getId(),
            $token
        )) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($category);
            $manager->flush();
        }

        return $this->redirectToRoute("store_category_show_all");
    }
}
