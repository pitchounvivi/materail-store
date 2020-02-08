<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{


    /**
     * @Route(
     *     "/new",
     *     name="user_new",
     *     methods={"GET","POST"}
     *     )
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function new(
        Request $request,
        UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //encodage du password
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_show_all');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     "/",
     *     name="user_show_all",
     *     methods={"GET"}
     *     )
     *
     *  @IsGranted("ROLE_MEMBER")
     *
     * @param UserRepository $repository
     * @return Response
     */
    public function showAll(UserRepository $repository): Response
    {
        return $this->render('user/show_all.html.twig', [
            'user' => $repository->findAll(),
        ]);
    }

    /**
     * @Route(
     *     "/{id<\d{1,3}>}",
     *     name="user_show",
     *     methods={"GET"}
     *     )
     *
     * @IsGranted("ROLE_MEMBER")
     *
     * @param User $user
     * @return Response
     */
    public function show(User $user): Response
    {
        // Créer une Vue et passer la Vue au Template
        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route(
     *     "/{id<\d{1,3}>}/edit",
     *     name="user_edit",
     *     methods={"GET","POST"}
     *     )
     *
     *  @IsGranted("ROLE_ADMIN")
     *
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_show', [
                "id" => $user->getId(),
            ]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     "/{id<\d{1,3}>}/delete",
     *     name="user_delete",
     *     methods={"DELETE"}
     *     )
     *
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request, User $user): Response
    {
        $token = $request->request->get('_token');


        if ($this->isCsrfTokenValid(
            'delete' . $user->getId(),
            $token
        )) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_show_all');
    }
}
