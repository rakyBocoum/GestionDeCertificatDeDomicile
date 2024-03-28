<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Personne;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
class FournirCertificatType extends AbstractType
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
        ->add("fournir", SubmitType::class, [
            'attr' => ['class' => "w-100 btn  btn-lg border border-white ","style"=>"background-color:#cfa0e9;"],
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
