<?php

namespace App\Form;

use App\Entity\Region;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\Departement;
use App\Repository\DepartementRepository;
use App\Repository\RegionRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;



class DemandeInscription1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('region', EntityType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
            ],
            'attr' => ['class' => 'form-select form-select-lg mb-3','aria-label' => '.form-select-lg example'],
            'class' => Region::class,
            'placeholder'=>'choisissez votre région?',
            'query_builder' => function (RegionRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.nom', 'ASC');
            },
            'choice_label' => 'nom',
            
        ])
        ->add("valider", SubmitType::class, [
            'attr' => ['class' => "w-100 btn  btn-lg  ", 'style'=>"background-color:#4B0082;color:#fff;border-color:#4B0082;"],
        ])
    ;

    $formModifier = function (FormInterface $form, Region $region = null) {
       
        if($region!=null)
        $form->add('departement', EntityType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'champ vide',
                ]),
            ],
            'attr' => ['class' => 'form-select form-select-lg mb-3','aria-label' => '.form-select-lg example'],
            'class' => Departement::class,
            'query_builder' => function (DepartementRepository $er) use ($region) {
                return $er->createQueryBuilder('u')
               ->where('u.region = :region')
               ->setParameter('region', $region)
               ->orderBy('u.nom', 'ASC');
            },
            'choice_label' => 'nom',
        ]);
        else{
            $form->add('departement', EntityType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ vide',
                    ]),
                ],
                'attr' => ['class' => 'form-select form-select-lg mb-3','aria-label' => '.form-select-lg example'],
                'class' => Departement::class,
                'placeholder' => 'il faut choisir une région.',
                'query_builder' => function (DepartementRepository $er) use ($region) {
                    return $er->createQueryBuilder('u')
                   ->where('u.region = :region')
                   ->setParameter('region', $region)
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
            $formModifier($event->getForm(), $data->getRegion());
            else{
                $formModifier($event->getForm(), null);
            }
        }
    );

    $builder->get('region')->addEventListener(
        FormEvents::POST_SUBMIT,
        function (FormEvent $event) use ($formModifier) {
            // It's important here to fetch $event->getForm()->getData(), as
            // $event->getData() will get you the client data (that is, the ID)
            $region = $event->getForm()->getData();

            // since we've added the listener to the child, we'll have to pass on
            // the parent to the callback function!
            $formModifier($event->getForm()->getParent(), $region);
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
