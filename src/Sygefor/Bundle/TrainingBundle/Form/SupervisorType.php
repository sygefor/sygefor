<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 6/13/16
 * Time: 11:48 AM.
 */
namespace Sygefor\Bundle\TrainingBundle\Form;

use Sygefor\Bundle\CoreBundle\Form\Type\VocabularyType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

class SupervisorType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('firstName', null, array(
                'label' => 'Prénom',
            ))
            ->add('email', EmailType::class, array(
                'label' => 'Email',
            ))
            ->add('phoneNumber', null, array(
                'label'    => 'Numéro de téléphone',
                'required' => false,
            ));
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return VocabularyType::class;
    }
}
