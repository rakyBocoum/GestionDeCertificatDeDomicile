<?php

namespace App\Form;

use App\Entity\Commune;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\Quartier;
use App\Repository\CommuneRepository;
use Symfony\Component\Form\FormEvent;
use App\Repository\QuartierRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotNull;

class DemandeInscription2Type extends AbstractType
{ 
    public function __construct(private RequestStack $requestStack)
    {
        
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $requestStack=$this->requestStack;
       
        $builder
        ->add('commune', EntityType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
            ],
            'attr' => ['class' => 'form-select form-select-lg mb-3','aria-label' => '.form-select-lg example'],
            'class' => Commune::class,
            'placeholder'=>'choisissez votre commune ?',
            'query_builder' => function (CommuneRepository $er) use ($requestStack){
                $session=$requestStack->getSession();
                return $er->createQueryBuilder('u')
                ->where('u.departement = :departement')
                ->setParameter('departement', $session->get('departement'))
                ->orderBy('u.nom', 'ASC');
            },
            'choice_label' => 'nom',
            
        ])
        ->add('photo', FileType::class,[
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
               new  File(
                    maxSize: '1024k',
                    mimeTypes: ['image/jpeg', 'image/jpg','image/png'],
                    mimeTypesMessage: 'veuillez entrez une image de type [jpg ou png ou jpeg]',
                )
            ]
        ])
        ->add('facture', FileType::class,[
            'attr' => ['class' => 'form-control','required' => ''],
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
               new  File(
                    maxSize: '1024k',
                    mimeTypes: ['image/jpeg', 'image/jpg','image/png'],
                    mimeTypesMessage: 'veuillez entrez une image de type [jpg ou png ou jpeg]',
                )
            ]
        ])
        ->add("valider", SubmitType::class, [
            'attr' => ['class' => "w-100 btn btn-success btn-lg border border-white ", 'style'=>"background-color:#4B0082;color:#fff;"],
        ])
    ;

    $formModifier = function (FormInterface $form, Commune $commune = null) {
       
        if($commune!=null)
        $form->add('quartier', EntityType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
            ],
            'attr' => ['class' => 'form-select form-select-lg mb-3','aria-label' => '.form-select-lg example'],
            'class' => Quartier::class,
            'query_builder' => function (QuartierRepository $er) use ($commune) {
                return $er->createQueryBuilder('u')
               ->where('u.commune = :commune')
               ->setParameter('commune', $commune)
               ->orderBy('u.nom', 'ASC');
            },
            'choice_label' => 'nom',
        ]);
        else{
            $form->add('quartier', EntityType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ vide',
                    ]),
                ],
                'attr' => ['class' => 'form-select form-select-lg mb-3','aria-label' => '.form-select-lg example'],
                'class' => Quartier::class,
                'placeholder' => 'il faut choisir un département.',
                'query_builder' => function (QuartierRepository $er) use ($commune) {
                    return $er->createQueryBuilder('u')
                   ->where('u.commune = :commune')
                   ->setParameter('commune', $commune)
                   ->orderBy('u.nom', 'ASC');
                },
                'choice_label' => 'nom',

            ]);

        }

    };

    $builder->addEventListener(
        FormEvents::PRE_SET_DATA,
        function (FormEvent $event) use ($formModifier) {
            // this would be your entity, i.e. SportMeetup
            $data = $event->getData();
            if($data!=null)
            $formModifier($event->getForm(), $data->getCommune());
            else{
                $formModifier($event->getForm(), null);
            }
        }
    );

    $builder->get('commune')->addEventListener(
        FormEvents::POST_SUBMIT,
        function (FormEvent $event) use ($formModifier) {
            // It's important here to fetch $event->getForm()->getData(), as
            // $event->getData() will get you the client data (that is, the ID)
            $commune = $event->getForm()->getData();

            // since we've added the listener to the child, we'll have to pass on
            // the parent to the callback function!
            $formModifier($event->getForm()->getParent(), $commune);
        }
    );
   
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
