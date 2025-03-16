<?php

namespace App\Form;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Emission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmissionSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('titre', TextType::class, [
            'required' => false,
            'label' => 'Titre de l\'émission',
            'attr' => [
                'placeholder' => 'Rechercher par titre'
            ]
        ])
        ->add('datepub', DateTimeType::class, [
            'required' => false,
            'widget' => 'single_text',
            'label' => 'Date de diffusion',
            'html5' => true, // Utilise le widget de date HTML5 natif
            'input' => 'datetime',
            'attr' => [
                'class' => 'form-control'
            ]
        ])
        ->add('categorie', EntityType::class, [
            'class' => Categories::class,
            'required' => false,
            'placeholder' => 'Sélectionnez une catégorie',// Texte par défaut
            'data' => null, // Assure qu'aucune valeur n'est sélectionnée par défaut
            'choice_label' => 'titre',
            'label'=> 'Catégorie',
            'query_builder' => function (CategoriesRepository $er): QueryBuilder {
                return $er->createQueryBuilder('u')
                    ->where('u.active = 1' )
                    ->orderBy('u.titre', 'ASC');
            }
        ]);
}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
