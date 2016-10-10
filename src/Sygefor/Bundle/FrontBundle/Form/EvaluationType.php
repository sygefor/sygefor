<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/26/16
 * Time: 5:42 PM
 */

namespace Sygefor\Bundle\FrontBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class EvaluationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('criteria', CollectionType::class, array(
                'label' => 'Critères d\'évaluation',
                'type' => EvaluationNotedCriterionType::class,
            ))
            ->add('message', null, array(
                'label' => 'Message',
                'required' => false,
                'attr' => array(
                    'placeholder' => "Vous pouvez éventuellement laisser un message qui accompagnera votre évaluation."
                )
            ));
    }
}