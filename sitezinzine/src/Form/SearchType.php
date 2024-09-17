<?php

namespace App\Form;

use App\Model\SearchData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
    ->add('q', TextType::class, [
        'attr' => [
            'placeholder' => 'Recherche'
        ]
        ])
        ->add('Sauvegarder', SubmitType::class)
        ->addEventListener(FormEvents::PRE_SUBMIT,  $this->autoKeyword(...))
        ;
        
}
public function configureOptions(OptionsResolver $resolver)
{
    $resolver->setDefaults([
        'data_class' => SearchData::class,
        'method' => 'GET', 
        'csrf_protection' => false
    ]);
}
public function autoKeyword(PreSubmitEvent $event):void
{
    $data=$event->getData();
    

}

}