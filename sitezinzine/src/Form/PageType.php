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
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $page = $event->getData();
            $form = $event->getForm();

            if (!$page) {
                return;
            }

            $isEdit = null !== $page->getId();

            // ✅ Édition : slug visible mais non modifiable
            if ($isEdit && $form->has('slug')) {
                $config = $form->get('slug')->getConfig();
                $options = $config->getOptions();
                $options['disabled'] = true;      // important : disabled, pas readonly
                $options['help'] = 'Non modifiable après création';

                $form->add('slug', TextType::class, $options);
            }

            // ✅ Checkbox uniquement si une image existe
            if ($page->getMainImageName()) {
                $form->add('deleteMainImage', CheckboxType::class, [
                    'required' => false,
                    'mapped'   => false,
                    'label'    => 'Supprimer l’image de tête',
                ]);
            } else {
                // si jamais tu avais une ancienne version qui l’ajoutait, on nettoie
                if ($form->has('deleteMainImage')) {
                    $form->remove('deleteMainImage');
                }
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
