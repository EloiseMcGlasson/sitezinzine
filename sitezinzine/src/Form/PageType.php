<?php

namespace App\Form;

use App\Entity\Page;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                    'class' => 'tinymce',
                    'rows' => 15,
                ],
            ])
            ->add('mainImageFile', FileType::class, [
                'label'    => 'Image de tête (optionnelle)',
                'required' => false,
            ])
            ->add('deleteMainImage', CheckboxType::class, [
                'required' => false,
                'mapped' => true,
                'label' => 'Supprimer l’image de tête',
            ])
        ;

        // 🔥 ICI : logique création vs édition
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $page = $event->getData();
            $form = $event->getForm();

            if (!$page || null === $page->getId()) {
                // ➜ CRÉATION : slug modifiable
                return;
            }

            // Édition : on SUPPRIME le champ du form => impossible à soumettre/modifier
            if ($form->has('slug')) {
                $form->remove('slug');
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
