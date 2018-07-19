<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/26/16
 * Time: 5:42 PM.
 */

namespace FrontBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use AppBundle\Entity\Evaluation\Evaluation;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EvaluationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('themes', CollectionType::class, array(
                'label' => 'Thèmes',
                'type' => EvaluatedThemeType::class,
            ))
            ->add('goodPoints', null, array(
                'label' => 'Les points forts de la formation',
                'required' => false,
            ))
            ->add('badPoints', null, array(
                'label' => 'Les points à améliorer',
                'required' => false,
            ))
            ->add('suggestions', null, array(
                'label' => 'Plan d’action et suggestions éventuelles',
                'required' => false,
            ))
        ;
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Evaluation::class,
        ));
    }
}
