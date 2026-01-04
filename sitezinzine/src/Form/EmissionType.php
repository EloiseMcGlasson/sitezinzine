<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\InviteOldAnimateur;
use App\Entity\Emission;
use App\Entity\Theme;
use App\Entity\Editeur;
use App\Repository\CategoriesRepository;
use App\Repository\ThemeRepository;
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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use App\Repository\UserRepository;




class EmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('categorie', EntityType::class, [
            'class' => Categories::class,
            'placeholder' => 'Sélectionnez une catégorie  (obligatoire)',
            'choice_label' => 'titre',
            'label' => 'Catégorie',
            'query_builder' => function (CategoriesRepository $er): QueryBuilder {
                return $er->createQueryBuilder('u')
                    ->where('u.active = 1')
                    ->orderBy('u.titre', 'ASC');
            }
        ])
        ->add('theme', EntityType::class, [
            'class' => Theme::class,
            'placeholder' => 'Sélectionnez un thème  (obligatoire)',
            'choice_label' => 'name',
            'label' => 'Thème',
            'query_builder' => function (ThemeRepository $ert): QueryBuilder {
                return $ert->createQueryBuilder('v')
                    ->orderBy('v.name', 'ASC');
            }
        ])
        ->add('editeur', EntityType::class, [
            'class' => Editeur::class,
            'choice_label' => 'name',
            'label' => 'Éditeur'
        ])

        // ✅ 2 listes séparées (non mappées)
        ->add('invites', EntityType::class, [
            'class' => InviteOldAnimateur::class,
            'mapped' => false,
            'required' => false,
            'multiple' => true,
            'expanded' => false,
            'label' => 'Invité·es',
            'choice_label' => fn(InviteOldAnimateur $a) => (string) $a,
            'query_builder' => fn(InviteOldAnimateurRepository $er): QueryBuilder
                => $er->createQueryBuilder('i')
                    ->andWhere('i.ancienanimateur = 0 OR i.ancienanimateur IS NULL')
                    ->orderBy('i.lastName', 'ASC'),
        ])
        ->add('anciensAnimateurs', EntityType::class, [
            'class' => InviteOldAnimateur::class,
            'mapped' => false,
            'required' => false,
            'multiple' => true,
            'expanded' => false,
            'label' => 'Ancien·nes animateur·ices',
            'choice_label' => fn(InviteOldAnimateur $a) => (string) $a,
            'query_builder' => fn(InviteOldAnimateurRepository $er): QueryBuilder
                => $er->createQueryBuilder('i')
                    ->andWhere('i.ancienanimateur = 1')
                    ->orderBy('i.lastName', 'ASC'),
        ])

        ->add('titre', TextType::class, [
            'label' => 'Titre de l\'émission  (obligatoire)',
        ])
        ->add('keyword', TextType::class, [
            'required' => false,
            'label' => 'Mot(s) clé(s)'
        ])
        ->add('ref', TextType::class, [
            'label' => 'Créateur/trice',
            'help' => 'À terme remplacé par “Utilisateur·ices”. Pour l’instant, laisse ce champ le temps de corriger les données.',
            'required' => false,
        ])
        ->add('users', EntityType::class, [
            'class' => User::class,
            'choice_label' => 'username',
            'label' => 'Utilisateur·ices',
            'required' => false,
            'multiple' => true,
            'expanded' => false,
            'query_builder' => fn(UserRepository $ur): QueryBuilder
                => $ur->createQueryBuilder('u')->orderBy('u.username', 'ASC'),
        ])
        ->add('duree', IntegerType::class, [
            'label' => 'Durée (obligatoire)'
        ])
        ->add('url', UrlType::class, [
            'required' => false,
            'default_protocol' => 'http',
            'label' => 'Url de l\'émission',
            'empty_data' => '',
        ])
        ->add('descriptif', TextareaType::class, [
            'empty_data' => 'Description à remplir',
            'label' => 'Descriptif (obligatoire)',
            'required' => false,
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
    ;

    // ✅ pré-remplir les 2 listes à partir de la relation réelle
    $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
        $emission = $event->getData();
        $form = $event->getForm();

        if (!$emission instanceof Emission) {
            return;
        }

        $invites = [];
        $anciens = [];

        foreach ($emission->getInviteOldAnimateurs() as $person) {
            if ($person->isAncienanimateur()) {
                $anciens[] = $person;
            } else {
                $invites[] = $person;
            }
        }

        $form->get('invites')->setData($invites);
        $form->get('anciensAnimateurs')->setData($anciens);
    });

    // ✅ fusionner les 2 champs vers la relation réelle
    $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
        $emission = $event->getData();
        $form = $event->getForm();

        if (!$emission instanceof Emission) {
            return;
        }

        foreach ($emission->getInviteOldAnimateurs()->toArray() as $person) {
            $emission->removeInviteOldAnimateur($person);
        }

        $invites = $form->get('invites')->getData() ?? [];
        $anciens = $form->get('anciensAnimateurs')->getData() ?? [];

        foreach ($invites as $p) {
            $emission->addInviteOldAnimateur($p);
        }
        foreach ($anciens as $p) {
            $emission->addInviteOldAnimateur($p);
        }
    });

    // tes listeners existants
    $builder->addEventListener(FormEvents::PRE_SUBMIT, $this->autoKeyword(...));
    $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
        $data = $event->getData();
        if (empty($data['ref']) && !empty($options['current_user_identifier'])) {
            $data['ref'] = $options['current_user_identifier'];
            $event->setData($data);
        }
    });
}


    public function autoKeyword(PreSubmitEvent $event): void
    {
        $data = $event->getData();
        if (empty($data['keyword'])) {
            $data['keyword'] = 'Keyword';
            $event->setData($data);
        }
    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Emission::class,
            'allow_extra_fields' => true,
            'current_user_identifier' => null,
        ]);
    }
}
