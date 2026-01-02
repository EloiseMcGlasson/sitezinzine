<?php

namespace App\Form;

use App\Entity\InviteOldAnimateur;
use Doctrine\DBAL\Types\BooleanType as TypesBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class InviteOldAnimateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom*',
                'required' => true,
                
            ])

            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])

            ->add('phoneNumber', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])

            ->add('mail', EmailType::class, [
                'label' => 'Email',
                'required' => false,
            ])

            ->add('ancienanimateur', CheckboxType::class, [
                'required' => false,
                'label' => 'Ancien·ne Animateurice'
            ])

            ->add('Sauvegarder', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InviteOldAnimateur::class,
        ]);
    }
}
