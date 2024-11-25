<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Invite;
use App\Entity\Emission;
use App\Entity\Theme;
use App\Entity\Editeur;
use App\Repository\CategoriesRepository;
use App\Repository\InviteRepository;
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
            'choice_label' => 'name',
            'label'=> 'Thème'
        ])
        ->add('editeur', EntityType::class, [
            'class' => Editeur::class,
            'choice_label' => 'name',
            'label'=> 'Éditeur'
        ])

        
        ->add('invites', EntityType::class, [
            'class' => Invite::class,
            'choice_label' => function (Invite $invite) {
                return $invite->getLastName() . ' ' . $invite->getFirstName();},
            'label'=> 'Invité·es',
            'required' => false,
            'multiple' => true, // Enable multiple selections
            'expanded' => false, // Display checkboxes instead of a dropdown
            'query_builder' => function (InviteRepository $er): QueryBuilder {
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
            'label'=> 'Créateur/trice'
        ])
        ->add('duree', IntegerType::class, [
            'label'=> 'Durée'
        ])
        ->add('url', UrlType::class, [
            'required' => false,
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
