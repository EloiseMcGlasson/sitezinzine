<?php

namespace App\Form;

use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'empty_data' => 'Nouvelle catégorie',
                'label'=> 'Titre de la catégorie',
                
            ])
            
            ->add('editeur', TextType::class, [
                
                'label'=> 'Éditeur',
            ])
            ->add('duree', IntegerType::class, [
                'label'=> 'Durée'
                ])
            ->add('descriptif', TextareaType::class, [
                'empty_data' => 'Description à remplir',
                'label'=> 'Descriptif',
            ])
           
            ->add('thumbnailFile', FileType::class, [
                'required' => false,
                'label' => 'Ajouter une image :'
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'label' => 'Cocher si la catégorie est active'
            ])
            ->add('Sauvegarder', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}
