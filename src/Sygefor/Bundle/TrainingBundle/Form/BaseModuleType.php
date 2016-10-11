<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 6/7/16
 * Time: 10:38 AM.
 */
namespace Sygefor\Bundle\TrainingBundle\Form;

use Sygefor\Bundle\CoreBundle\Form\Type\EntityHiddenType;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractModule;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BaseModuleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                'label' => 'Nom',
            ))
            ->add('mandatory', null, array(
                'label'    => 'Obligatoire',
                'required' => false,
            ))
//            ->add('training', EntityHiddenType::class, array(
//                'label'    => 'Formation',
//                'class'    => AbstractTraining::class,
//                'required' => true,
//            ))
            ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AbstractModule::class,
        ));

        parent::setDefaultOptions($resolver);
    }
}
