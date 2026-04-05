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
            ->add('recurrenceType', ChoiceType::class, [
                'label' => 'Type de récurrence',
                'choices' => [
                    'Hebdomadaire' => ProgrammationRuleSlot::RECURRENCE_WEEKLY,
                    'Mensuelle' => ProgrammationRuleSlot::RECURRENCE_MONTHLY,
                ],
            ])

            ->add('monthlyOccurrence', ChoiceType::class, [
                'label' => 'Position dans le mois',
                'choices' => [
                    '1er' => ProgrammationRuleSlot::MONTHLY_FIRST,
                    '2e' => ProgrammationRuleSlot::MONTHLY_SECOND,
                    '3e' => ProgrammationRuleSlot::MONTHLY_THIRD,
                    '4e' => ProgrammationRuleSlot::MONTHLY_FOURTH,
                    'Dernier' => ProgrammationRuleSlot::MONTHLY_LAST,
                ],
                'required' => false,
                'placeholder' => 'Non applicable (hebdomadaire)',
                'help' => 'Utilisé uniquement pour les programmations mensuelles.',
            ])

            ->add('monthInterval', IntegerType::class, [
                'label' => 'Intervalle mensuel',
                'attr' => [
                    'min' => 1,
                    'placeholder' => '1 = tous les mois',
                ],
                'help' => 'Utilisé uniquement pour les programmations mensuelles. 1 = tous les mois, 2 = tous les 2 mois…',
            ])

            ->add('dayOfWeek', ChoiceType::class, [
                'label' => 'Jour',
                'choices' => [
                    'Mardi' => 2,
                    'Mercredi' => 3,
                    'Jeudi' => 4,
                    'Vendredi' => 5,
                    'Samedi' => 6,
                    'Dimanche' => 7,
                    'Lundi' => 1,
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
                'label' => 'Durée (minutes)',
                'attr' => [
                    'min' => 1,
                    'placeholder' => 'Ex. 60',
                ],
            ])

            ->add('broadcastRank', IntegerType::class, [
                'label' => 'Ordre de diffusion',
                'attr' => [
                    'min' => 1,
                ],
                'help' => '1 = 1re diffusion, 2 = rediffusion 1, 3 = rediffusion 2…',
            ])

            ->add('weekOffset', IntegerType::class, [
                'label' => 'Décalage de semaine radio',
                'attr' => [
                    'min' => 0,
                    'placeholder' => '0 = même semaine',
                ],
                'help' => '0 = même semaine radio, 1 = semaine suivante, 2 = deux semaines plus tard…',
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