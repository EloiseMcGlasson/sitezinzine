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

        $existingType = trim($options['data']->getType()); // Supprime les espaces invisibles
        $existingType = ucfirst(strtolower($existingType)); // Normalise la casse (1ère lettre majuscule, le reste minuscule)
       
        
        $choices = [
            'Concert' => 'Concert',
            'Spectacle' => 'Spectacle',
            'Exposition' => 'Exposition',
            'Festival' => 'Festival',
            'Cinéma' => 'Cinéma',
            'Randonnée' => 'Randonnée',
            'Conférence - Débat' => 'Conférence - Débat',
            'Stage - Cours - Atelier' => 'Stage - Cours - Atelier',
            'Rassemblement - Manifestation' => 'Rassemblement - Manifestation',
            'Autre' => 'autre'
        ];
        
        $autreTypeValue = '';
$typeValue = $existingType;

// ✅ Si le type existant n'est pas dans la liste, il est considéré comme un type personnalisé
if (!in_array($existingType, $choices, true) && !empty($existingType)) {
    $autreTypeValue = $existingType;
    $typeValue = 'autre'; // Forcer la sélection de "Autre" si un type personnalisé est trouvé
}
  
        
        
       
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
                'choices' => $choices,
                'placeholder' => 'Sélectionnez un type d\'évènement',
                'data' => $typeValue, // ✅ Sélectionne correctement "Autre" si besoin
                'choice_label' => fn ($choice, $key, $value) => $key,
                'choice_value' => fn ($choice) => strtolower($choice), // 🔥 Normalisation
            ])
            ->add('autreType', TextType::class, [
                'label' => 'Autre type',
                'required' => false,
                'mapped' => false, // Ne lie pas cette propriété à l'entité
                'data' => $autreTypeValue, // ✅ Remplit l'input si un type personnalisé est déjà sélectionné
                'attr' => ['style' => ($autreTypeValue ? 'display:block;' : 'display:none;')], // Cache si pas nécessaire
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
