<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;
use getID3;

class GetID3Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, [
            'label' => 'Titre',
            'required' => false,
        ])
        ->add('artiste', TextType::class, [
            'label' => 'Créateur/trice',
            'required' => false,
        ])
        ->add('album', TextType::class, [
            'label' => 'Catégorie',
            'required' => false,
        ])
        ->add('comment', TextType::class, [
            'label' => 'Présentation de l\'émission',
            'required' => false,
        ])
        ->add('genre', TextType::class, [
            'label' => 'Genre',
            'required' => false,
        ])
        ->add('recordingTime', IntegerType::class, [
            'label' => 'Durée d\'enregistrement',
            'required' => false,
        ])
        ->add('language', TextType::class, [
            'label' => 'Langue',
            'required' => false,
        ])
        ->add('publisher', TextType::class, [
            'label' => 'Éditeur',
            'required' => false,
        ])
        ->add('year', IntegerType::class, [
            'label' => 'Année',
            'required' => false,
        ])
        ->add('logo', FileType::class, [
            'label' => 'Logo (PNG file)',
            'required' => false,
            'constraints' => [
                new File([
                    'mimeTypes' => ['image/png'],
                    'mimeTypesMessage' => 'Please upload a valid PNG file',
                ])
            ],
        /* $builder
            ->add('mp3File', FileType::class, [
                'label' => 'Fichier MP3',
                'required' => true,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'audio/mpeg',
                            'audio/mp3',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier MP3 valide.',
                    ])
                ],*/
            ]); 
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Assurez-vous que cette classe est correcte
        ]);
    }
}
