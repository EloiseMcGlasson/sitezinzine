<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\User;
use App\Entity\InviteOldAnimateur;
use App\Repository\CategoriesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieType extends AbstractType
{
    public function __construct(
        private CategoriesRepository $categoriesRepository
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $editeursRaw = $this->categoriesRepository->findDistinctEditeursWithNames();

        $editeursChoices = [];
        foreach ($editeursRaw as $row) {
            $editeursChoices[$row['name']] = (int) $row['id'];
        }

        $builder
            ->add('titre', TextType::class, [
                'empty_data' => 'Nouvelle catégorie',
                'label' => 'Titre de la catégorie',
            ])

            ->add('editeur', ChoiceType::class, [
                'label' => 'Éditeur',
                'choices' => $editeursChoices,
                'placeholder' => 'Choisir un éditeur',
                'required' => true,
            ])

            ->add('duree', IntegerType::class, [
                'label' => 'Durée'
            ])

            ->add('descriptif', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control tinymce',
                    'rows' => 10
                ]
            ])

            // ✅ Users (ManyToMany)
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'multiple' => true,
                'required' => false,
                'label' => 'Utilisateur·ices (comptes)',
            ])

            // ✅ Anciens animateurs
            ->add('inviteOldAnimateurs', EntityType::class, [
                'class' => InviteOldAnimateur::class,
                'multiple' => true,
                'required' => false,
                'label' => 'Ancien·nes animateur·ices',
            ])

            ->add('thumbnailFile', FileType::class, [
                'required' => false,
                'label' => 'Ajouter une image :'
            ])

            ->add('active', CheckboxType::class, [
                'required' => false,
                'label' => 'Cocher si la catégorie est active'
            ])

            ->add('Sauvegarder', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}
