<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\InviteOldAnimateur;
use App\Entity\Emission;
use App\Entity\Theme;
use App\Entity\Editeur;
use App\Repository\CategoriesRepository;
use App\Repository\InviteOldAnimateurRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;



class EmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

        ->add('categorie', EntityType::class, [
            'class' => Categories::class,
            'placeholder' => 'Sélectionnez une catégorie',// Texte par défaut
            'choice_label' => 'titre',
            'label'=> 'Catégorie',
            'query_builder' => function (CategoriesRepository $er): QueryBuilder {
                return $er->createQueryBuilder('u')
                    ->where('u.active = 1' )
                    ->orderBy('u.titre', 'ASC');
            }
        ])
        ->add('theme', EntityType::class, [
            'class' => Theme::class,
            'placeholder' => 'Sélectionnez un thème',// Texte par défaut
            'choice_label' => 'name',
            'label'=> 'Thème'
        ])
        ->add('editeur', EntityType::class, [
            'class' => Editeur::class,
            'choice_label' => 'name',
            'label'=> 'Éditeur'
        ])

        
        ->add('InviteOldAnimateurs', EntityType::class, [
            'class' => InviteOldAnimateur::class,
            'choice_label' => function (InviteOldAnimateur $InviteOldAnimateur) {
                return $InviteOldAnimateur->getLastName() . ' ' . $InviteOldAnimateur->getFirstName() . ' ' . $InviteOldAnimateur->isAncienanimateur();},
            'label'=> 'Invité·es Animateurices',
            'required' => false,
            'multiple' => true, // Enable multiple selections
            'expanded' => false, // Display checkboxes instead of a dropdown
            'query_builder' => function (InviteOldAnimateurRepository $er): QueryBuilder {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.lastName', 'ASC');}
        ])

        ->add('titre', TextType::class, [
            'label'=> 'Titre de l\'émission',
            //restreindre le nb de char à 45 attention ne pas changer la bdd car on perd les titre trop long qui sont coupés
        ])
        ->add('keyword', TextType::class, [
            'required' => false,
            'label'=> 'Mot(s) clé(s)'
        ])
        ->add('ref', TextType::class, [
            'label'=> 'Créateur/trice',
            'required' => false,
        ])
        ->add('duree', IntegerType::class, [
            'label'=> 'Durée'
        ])
        ->add('url', UrlType::class, [
            'required' => false,
            'default_protocol' => 'http',
            'label'=> 'Url de l\'émission'
            
        ])
        ->add('descriptif', TextareaType::class, [
            'empty_data' => 'Description à remplir',
            'label'=> 'Descriptif',
        ])
    
        ->add('thumbnailFile', FileType::class, [
            'required' => false,
            'label' => 'Ajouter une image :'
        ])

        ->add('thumbnailFileMp3', FileType::class, [
            'required' => false,
            'label' => 'Ajouter un Mp3 :'
        ])
        

        ->add('Sauvegarder', SubmitType::class)
        ->addEventListener(FormEvents::PRE_SUBMIT, $this->autoKeyword(...))
        
        ;
    }

    public function autoKeyword(PreSubmitEvent $event):void
    {
        $data=$event->getData();
        if (empty($data['keyword'])) {
            $data['keyword']='Keyword';
            $event->setData($data);
        }

    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Emission::class,
            'allow_extra_fields' => true,
        ]);
    }
}
