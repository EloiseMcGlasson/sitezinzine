<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Emission;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('keyword')
            ->add('ref')
            ->add('duree')
            ->add('url')
            ->add('descriptif')
            ->add('categorie', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'titre',
            ])
            ->add('Sauvegarder', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Emission::class,
        ]);
    }
}
