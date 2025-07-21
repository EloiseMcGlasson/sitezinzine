<?php

namespace App\Form;

use App\Entity\Diffusion;
use App\Entity\Emission;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiffusionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('horaireDiffusion', null, [
                'widget' => 'single_text',
            ])
            ->add('nombreDiffusion')
            ->add('emission', EntityType::class, [
                'class' => Emission::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Diffusion::class,
        ]);
    }
}
