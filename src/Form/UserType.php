<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('password')
            ->add('email')
            ->add('role', ChoiceType::class,[
                'choices' => [
                    'Admin'=> 'ROLE_ADMIN',
                    'Member'=> 'ROLE_MEMBER',
                    'Visitor'=>'ROLE_VISITOR'
                ],
            ]);

            //si on veut mettre la validation ici et pas dans l'entity
            //Voir la doc dans FormType option
            //en exemple
//          ->add('email', EmailType::class, [
//            'constraints'=> [
//                new NotBlank(),
//                new Email()

                //option pour mettre une complexité comme pour un password
//                new Regex([
//                    "pattern"=> "(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^a-zA-Z\d]).*",
//                    "message"=> "Pattern does not match"
//                ])

//            ]
//])


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
