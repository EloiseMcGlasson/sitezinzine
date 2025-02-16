<?php

namespace App\Form;

use App\Entity\Annonce;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        if ($options['show_annonce']) {
            $builder->add('titre', TextType::class, [
                'label' => 'Titre',
            ]);}
            if ($options['show_annonce']) {
                $builder->add('organisateur', TextType::class, [
                'label' => 'Organisateur',
            ]);}
            if ($options['show_annonce']) {
                $builder->add('ville', TextType::class, [
                'label' => 'Ville',
            ]);}
            if ($options['show_annonce']) {
                $builder->add('departement', TextType::class, [
                'label' => 'Département',
            ]);}
            if ($options['show_annonce']) {
                $builder->add('adresse', TextType::class, [
                'label' => 'Adresse',
            ]);}
            if ($options['show_annonce']) {
                $builder->add('dateDebut', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'label' => 'Date de début',
                'widget' => 'single_text',
            ]);}
            if ($options['show_annonce']) {
                $builder->add('dateFin', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'label' => 'Date de fin',
                'widget' => 'single_text',

            ]);}
            if ($options['show_annonce']) {
                $builder->add('horaire', TextType::class, [
                'label' => 'Horaire',
            ]);}
            if ($options['show_annonce']) {
                $builder->add('prix', TextType::class, [
                'label' => 'Prix',
            ]);}
            if ($options['show_annonce']) {
                $builder->add('presentation', TextareaType::class, [
                'label' => 'Présentation',
                'empty_data' => 'Description à remplir',
            ]);}
            if ($options['show_annonce']) {
                $builder->add('contact', TextType::class, [
                'label' => 'Contact',
            ]);
        }
        if ($options['show_annonce']) {
            $builder->add('type', TextType::class, [
                'label' => 'Type',
            ]);
        }
        if ($options['show_valid']) {
            $builder->add('valid', CheckboxType::class, [
                'label' => 'Valide',
                'required' => false,
            ]);
        }
        if ($options['show_annonce']) {
            $builder->add('thumbnailFile', FileType::class, [
            'required' => false,
            'label' => 'Ajouter une image :',
            ]);
        }
        if ($options['show_valid'] or $options['show_annonce']) {
            $builder->add('Sauvegarder', SubmitType::class);
        
        }
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
            'show_valid' => false, // Option par défaut
            'show_annonce' => true, // Option par défaut
        ]);
    }
}
