<?php

namespace App\Form;

use App\Entity\Personne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\ContainsAlphanumericValidaror;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\DateValidator;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\LessThan;

class RegistrationFormType extends AbstractType
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
                    new Regex([
                        'message'=>"Ce format n'est pas valide",
                        'match'=>'false',
                        'pattern' => '/^[a-z]+$/i',
                        'htmlPattern' => '[a-zA-Z]+',
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
                    new Regex([
                        'message'=>"Ce format n'est pas valide",
                        'match'=>'false',
                        'pattern' => '/^[a-z]+$/i',
                        'htmlPattern' => '[a-zA-Z]+',
                    ]),
                ],
            ])
            ->add('telephone', NumberType::class, [
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
                    new Regex([
                        'message'=>"Ce format n'est pas valide",
                        'match'=>'false',
                        'pattern' => '/^[a-z]+$/i',
                        'htmlPattern' => '[a-zA-Z]+',
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
                    new Regex([
                        'message'=>"Ce format n'est pas valide",
                        'match'=>'false',
                        'pattern' => '/^[a-z]+$/i',
                        'htmlPattern' => '[a-zA-Z]+',
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
                    new LessThan([
                        'value' => (new \DateTime())->format('Y-m-d'),
                        'message' => 'La date saisie doit être inférieure à la date d\'aujourd\'hui.'
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
            ->add('agreeTerms', CheckboxType::class, [
                'attr' => ['class' => 'form-check-input','required' => ''],
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'JACCEPTETE LES CONDITIONS',
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
                'attr' => ['class' => "w-100 btn btn-lg btn-success"],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personne::class,
        ]);
    }
}
