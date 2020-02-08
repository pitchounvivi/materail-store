<?php


namespace App\Service\Form;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

class FlusherService
{
    private
        /**
         * @var EntityManagerInterface
         */
        $manager,

        /**
         * @var VerifierService
         */
        $verifier;

    /**
     * FlusherService constructor.
     * @param EntityManagerInterface $manager
     * @param VerifierService $verifier
     */
    public function __construct(EntityManagerInterface $manager, VerifierService $verifier)
    {
        $this->manager = $manager;
        $this->verifier = $verifier;
    }

    /**
     * @param FormInterface $form
     * @param string $message
     * @param bool $persist
     * @return bool
     */
    public function flush(
        FormInterface $form,
        string $message,
        bool $persist=false
    ):bool
    {
        try {
            if ($this->verifier->verify($form)) {
                if ($persist) {
                    $this->manager->persist($form->getData());
                }
                $this->manager->flush();
                return true;
            }
        } catch (UniqueConstraintViolationException $exc) {
            $form->addError(new FormError($message));
        }
        return false;
    }
}