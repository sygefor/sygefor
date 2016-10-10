<?php

namespace Sygefor\Bundle\ApiBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegistrationType.
 */
class RegistrationType extends ProfileType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('password', null,
          array(
            'label'         => 'Mot de passe',
            'property_path' => 'plainPassword',
          ));
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'       => false,
            'validation_groups'     => array('Default', 'trainee', 'api.profile', 'api.registration'),
            'enable_security_check' => false,
            'allow_extra_fields'    => true,
        ));
    }
}
