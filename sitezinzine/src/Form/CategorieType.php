<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Emission;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'empty_data' => 'Nouvelle catégorie'
            ])
            ->add('oldid')
            ->add('editeur')
            ->add('duree')
            ->add('descriptif')
           /*  ->add('emissions', EntityType::class, [
                'class' => Emission::class, 
                'choice_label' => 'titre', 
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false
            ]) */
            ->add('Sauvegarder', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}
