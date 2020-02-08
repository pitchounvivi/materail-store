<?php


namespace App\Service\Form;


use Symfony\Component\Form\FormInterface;

class VerifierService
{
    /**
     * VerifierService constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param FormInterface $form
     * @return bool
     */
    public function verify(FormInterface $form): bool
    {
        return $form->isSubmitted() && $form->isValid();

//        //equivalent de
//        if ($form->isSubmitted() && $form->isValid()) {
//            return true;
//        }
//        return false;

    }
}