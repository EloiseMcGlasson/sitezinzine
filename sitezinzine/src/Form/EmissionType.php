<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Emission;
use App\Entity\Theme;
use App\Entity\User;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
            ->add('titre', TextType::class, [
                'empty_data' => 'Nouvelle émission'
            ])
            ->add('keyword', TextType::class, [
                'required' => false
            ])
            ->add('ref')
            ->add('duree')
            ->add('url', UrlType::class)
            ->add('descriptif', TextareaType::class, [
                'empty_data' => 'Description à remplir'
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'titre',
            ])
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'name',
            ])
            ->add('thumbnailFile', FileType::class)
            

            ->add('Sauvegarder', SubmitType::class)
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->autoKeyword(...))
            ->addEventListener(FormEvents::PRE_SUBMIT,$this->autoUser(...))
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

    public function autoUser(PreSubmitEvent $event)
    {
        $data=$event->getData();
        if (empty($data['user_id'])) {
            $data['user_id']='Keyword';
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
