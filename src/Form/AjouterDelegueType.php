<?php

namespace App\Form;

use App\Entity\Commune;
use App\Entity\Personne;
use App\Entity\Quartier;
use App\Repository\QuartierRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AjouterDelegueType extends AbstractType
{
   
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
        ->add('lieunaissance', TextType::class, [
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
                new Length([
                    'min' => 2,
                    'minMessage' => 'le nombre de caractére est compris entre [2-50] ',
                    // max length allowed by Symfony for security reasons
                    'max' => 50,
                ]),
            ],
        ])
        ->add('fonction', TextType::class, [
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
                new Length([
                    'min' => 2,
                    'minMessage' => 'le nombre de caractére est compris entre [2-50] ',
                    // max length allowed by Symfony for security reasons
                    'max' => 50,
                ]),
            ],
        ])
        ->add('telephone', TextType::class, [
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
                new Length([
                    'min' => 7,
                    'minMessage' => 'nombre de caractére incorrecte',
                    // max length allowed by Symfony for security reasons
                    'max' => 50,
                ]),
            ],
        ])
        ->add('nom', TextType::class, [
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
                new Length([
                    'min' => 2,
                    'minMessage' => 'le nombre de caractére est compris entre [2-50] ',
                    // max length allowed by Symfony for security reasons
                    'max' => 30,
                ]),
            ],
        ])
        ->add('prenom', TextType::class, [
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
                new Length([
                    'min' => 2,
                    'minMessage' => 'le nombre de caractére est compris entre [2-50] ',
                    // max length allowed by Symfony for security reasons
                    'max' => 50,
                ]),
            ],
        ])
        ->add('datenaissance', DateType::class, [
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
            ],
        ])
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
        ->add('plainPassword', RepeatedType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'type' => PasswordType::class,
            'invalid_message' => 'mot de passe non conforme',
            'mapped' => false,
            'options' => ['attr' => ['autocomplete' => 'new-password','class' => 'form-control motde','required' => '']],
            'constraints' => [
                new NotBlank([
                    'message' => 'entrez un mot de passe',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'nombre de caractére est comprise entre [6-20]',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ])
        ->add("valider", SubmitType::class, [
            'attr' => ['class' => "w-100 btn  btn-lg border-white ",'style'=>"background-color:#4B0082;color:#ffff;"],
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }

   
}
