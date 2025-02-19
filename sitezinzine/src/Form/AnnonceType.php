<?php

namespace App\Form;

use App\Entity\Annonce;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
       
            $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('organisateur', TextType::class, [
                'label' => 'Organisateur',
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
            ])
            ->add('departement', TextType::class, [
                'label' => 'Département',
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('dateDebut', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('dateFin', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'label' => 'Date de fin',
                'widget' => 'single_text',

            ])
            ->add('horaire', TextType::class, [
                'label' => 'Horaire',
            ])
            ->add('prix', TextType::class, [
                'label' => 'Prix',
            ])
            ->add('presentation', TextareaType::class, [
                'label' => 'Présentation',
                'empty_data' => 'Description à remplir',
            ])
            ->add('contact', TextType::class, [
                'label' => 'Contact',
            ])
        
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Concert' => 'Concert',
                    'Spectacle' => 'Spectacle',
                    'Exposition' => 'Exposition',
                    'Festival' => 'Festival',
                    'Cinéma' => 'Cinéma',
                    'Randonnée' => 'Randonnée',
                    'Conférence - Débat' => 'Conférence - Débat',
                    'Stage - Cours - Atelier' => 'Stage - Cours - Atelier',
                    'Rassemblement - Manifestation' => 'Rassemblement - Manifestation',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'Sélectionnez un type d\'évènement', // Optionnel, affiche un choix vide par défaut
            ])

            ->add('autreType', TextType::class, [
                'label' => 'Autre type',
                'required' => false,
                
                'mapped' => false, // Ne lie pas cette propriété à l'entité
            ])
        
                
            ->add('thumbnailFile', FileType::class, [
            'required' => false,
            'label' => 'Ajouter une image :',
            ]);

            if ($options['show_valid']) {
                $builder->add('valid', CheckboxType::class, [
                    'label' => 'Valide',
                    'required' => false,
                ]);
            }
        
       
            $builder->add('Sauvegarder', SubmitType::class);
        
        
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
            'show_valid' => false, // Option par défaut
            
        ]);
    }
}
