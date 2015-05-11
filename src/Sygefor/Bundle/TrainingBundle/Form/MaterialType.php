<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 10/07/14
 * Time: 14:35
 */

namespace Sygefor\Bundle\TrainingBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class MaterialType
 * @package Sygefor\Bundle\TrainingBundle\Form
 */
class MaterialType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'file', array(
                'attr' => array('multiple' => true),
                'label' => 'Supports',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'material';
    }


}
