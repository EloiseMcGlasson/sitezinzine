<?php

namespace App\Form;

use App\Entity\ProgrammationRuleSlot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProgrammationRuleSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dayOfWeek', ChoiceType::class, [
                'label' => 'Jour',
                'choices' => [
                    'Lundi' => 1,
                    'Mardi' => 2,
                    'Mercredi' => 3,
                    'Jeudi' => 4,
                    'Vendredi' => 5,
                    'Samedi' => 6,
                    'Dimanche' => 7,
                ],
                'placeholder' => 'Choisir un jour',
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'with_seconds' => false,
            ])
            ->add('durationMinutes', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'attr' => [
                    'min' => 1,
                    'step' => 1,
                    'placeholder' => 'Ex. 60',
                ],
            ])
            ->add('broadcastRank', IntegerType::class, [
                'label' => 'Ordre de diffusion',
                'attr' => [
                    'min' => 1,
                    'step' => 1,
                    'placeholder' => '1 = 1re diffusion, 2 = rediffusion 1, etc.',
                ],
                'help' => '1 = 1re diffusion, 2 = rediffusion 1, 3 = rediffusion 2, etc.',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Créneau actif',
                'required' => false,
            ])
            ->add('Sauvegarder', SubmitType::class, [
                'label' => 'Sauvegarder',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProgrammationRuleSlot::class,
        ]);
    }
}