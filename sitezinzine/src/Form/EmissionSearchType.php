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
       ->add('dateDebut', DateTimeType::class, [
            'required' => false,
            'label' => 'Date de début',
            'widget' => 'single_text',
            'html5' => true,
        ])
        ->add('dateFin', DateTimeType::class, [
            'required' => false,
            'label' => 'Date de fin',
            'widget' => 'single_text',
            'html5' => true,
        ])
        ->add('categorie', EntityType::class, [
            'class' => Categories::class,
            'required' => false,
            'placeholder' => 'Sélectionnez une catégorie',// Texte par défaut
            'data' => null, // Assure qu'aucune valeur n'est sélectionnée par défaut
            'empty_data' => null, // ✅ Permet d'éviter qu'un ID par défaut soit utilisé
            'choice_label' => 'titre',
            'label'=> 'Catégorie',
            'query_builder' => function (CategoriesRepository $er): QueryBuilder {
                return $er->createQueryBuilder('u')
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
