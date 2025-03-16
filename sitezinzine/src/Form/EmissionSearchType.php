<?php

namespace App\Form;


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
