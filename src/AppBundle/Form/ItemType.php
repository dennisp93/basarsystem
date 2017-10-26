<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Beschreibung*'
            ])
            ->add('count', TextType::class, [
                'label' => 'Anzahl der Teile*'
            ])
            ->add('size', TextType::class, [
                'label' => 'Größe*'
            ])
            ->add('minPrice', TextType::class, [
                'label' => 'Verhandlungsbasis Preis in €'
            ])
            ->add('maxPrice', TextType::class, [
                'label' => 'Preis in €*'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern'
            ])
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Item'
        ));
    }
}
