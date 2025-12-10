<?php

namespace App\Form;

use App\Entity\Page;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('slug', TextType::class, [
                'label' => 'Identifiant de la page (slug)',
                'help'  => 'Ex : soutien, a-propos, infos-pratiques',
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'tinymce', // classe que tu utilises déjà pour TinyMCE
                    'rows' => 15,
                ],
            ])
            ->add('mainImageFile', FileType::class, [
                'label'    => 'Image de tête (optionnelle)',
                'required' => false,
                'mapped'   => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
