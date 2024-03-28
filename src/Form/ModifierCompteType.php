<?php

namespace App\Form;

use App\Entity\Personne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ModifierCompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('lieunaissance', TextType::class, [
            'required' => $options['require_due_date'],
            'required' => false,
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
              
                new Length([
                    'min' => 2,
                    'minMessage' => 'le nombre de caractére est compris entre [2-50] ',
                    // max length allowed by Symfony for security reasons
                    'max' => 50,
                ]),
            ],
        ])
        ->add('nom', TextType::class, [
            'required' => $options['require_due_date'],
            'required' => false,
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
                new Length([
                    'min' => 2,
                    'minMessage' => 'le nombre de caractére est compris entre [2-50] ',
                    // max length allowed by Symfony for security reasons
                    'max' => 30,
                ]),
            ],
        ])
        ->add('prenom', TextType::class, [
            'required' => $options['require_due_date'],
            'required' => false,
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
                new Length([
                    'min' => 2,
                    'minMessage' => 'le nombre de caractére est compris entre [2-50] ',
                    // max length allowed by Symfony for security reasons
                    'max' => 50,
                ]),
            ],
        ])
        ->add('datenaissance', DateType::class, [
            'required' => $options['require_due_date'],
            'required' => false,
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
            ],
        ])
        
        ->add('fonction', TextType::class, [
            'required' => $options['require_due_date'],
            'required' => false,
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new Length([
                    'min' => 2,
                    'minMessage' => 'le nombre de caractére est compris entre [2-50] ',
                    // max length allowed by Symfony for security reasons
                    'max' => 50,
                ]),
            ],
        ])
        ->add('telephone', TextType::class, [
            'required' => $options['require_due_date'],
            'required' => false,
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new Length([
                    'min' => 7,
                    'minMessage' => 'nombre de caractére incorrecte',
                    // max length allowed by Symfony for security reasons
                    'max' => 50,
                ]),
            ],
        ])
      
        ->add('email', EmailType::class, [
            'required' => $options['require_due_date'],
            'required' => false,
            'attr' => ['class' => 'form-control','placeholder' => 'sama-domicile@gmail.com','type'=>'email'],
            'constraints' => [
                new Email([
                    'message' => 'adresse mail incorrecte.',
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
            'required' => $options['require_due_date'],
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'required' => false,
            'type' => PasswordType::class,
            'invalid_message' => 'mot de passe non conforme',
            'mapped' => false,
            'options' => ['attr' => ['autocomplete' => 'new-password','class' => 'form-control motde']],
            'constraints' => [
                new Length([
                    'min' => 6,
                    'minMessage' => 'nombre de caractére est comprise entre [6-20]',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ])
        ->add("valider", SubmitType::class, [
            'attr' => ['class' => "w-100 btn  btn-lg border border-white ","style"=>"background-color:#4B0082;"],
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personne::class,
            'require_due_date' => false,
        ]);
        $resolver->setAllowedTypes('require_due_date', 'bool');
    }
}
