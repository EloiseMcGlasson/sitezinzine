<?php

namespace App\Form;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmissionSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('titre', TextType::class, [
            'required' => false,
            'label' => 'Rechercher par mot',
            'attr' => [
                'placeholder' => 'Rechercher par mot'
            ]
        ])
       ->add('dateDebut', DateType::class, [
            'required' => false,
            'label' => 'Date de début',
            'widget' => 'single_text',
            'html5' => false,
        ])
        ->add('dateFin', DateType::class, [
            'required' => false,
            'label' => 'Date de fin',
            'widget' => 'single_text',
            'html5' => false,
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
        ])
        ->add('theme', EntityType::class, [
            'class' => Theme::class,
            'required' => false,
            'placeholder' => 'Sélectionnez un thème',// Texte par défaut
            'data' => null, // Assure qu'aucune valeur n'est sélectionnée par défaut
            'empty_data' => null, // ✅ Permet d'éviter qu'un ID par défaut soit utilisé
            'choice_label' => 'name',
            'label'=> 'Thème',
            'query_builder' => function (ThemeRepository $er): QueryBuilder {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.name', 'ASC');
            }
        ]);
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
