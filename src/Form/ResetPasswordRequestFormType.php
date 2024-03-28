<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;


class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'attr' => ['class' => 'form-control','required' => '','placeholder' => 'sama-domicile@gmail.com','type'=>'email'],
            'constraints' => [
                new Email([
                    'message' => 'adresse mail incorrecte.',
                ]),
                new NotBlank([
                    'message' => 'champ vide',
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => 'le nombre de caractére est compris entre [3-180] ',
                    // max length allowed by Symfony for security reasons
                    'max' => 180,
                ]),
            ],
        ])
            ->add("valider", SubmitType::class, [
                'attr' => ['class' => "w-100 btn btn-primary btn-lg border border-white "],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
