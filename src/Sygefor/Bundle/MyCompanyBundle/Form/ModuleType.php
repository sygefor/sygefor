<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/14/16
 * Time: 5:35 PM
 */

namespace Sygefor\Bundle\MyCompanyBundle\Form;


use Sygefor\Bundle\MyCompanyBundle\Entity\LongTraining;
use Sygefor\Bundle\MyCompanyBundle\Entity\Module;
use Sygefor\Bundle\CoreBundle\Form\Type\EntityHiddenType;
use Sygefor\Bundle\TrainingBundle\Form\BaseModuleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ModuleType
 * @package Sygefor\Bundle\MyCompanyBundle\Form
 */
class ModuleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', null, array(
                'label' => 'Nom',
            ))
            ->add('mandatory', null, array(
                'label'    => 'Obligatoire',
                'required' => false,
            ))
            ->add('training', EntityHiddenType::class, array(
                'label'    => 'Formation longue',
                'class'    => LongTraining::class,
                'required' => true,
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => Module::class,
        ));
    }
}