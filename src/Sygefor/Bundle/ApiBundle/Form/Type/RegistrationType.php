<?php
namespace Sygefor\Bundle\ApiBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class RegistrationType
 * @package Sygefor\Bundle\ApiBundle\Form\Type
 */
class RegistrationType extends ProfileType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('password', null,
          array(
            'label' => 'Mot de passe',
            'property_path' => 'plainPassword'
          ));
    }
    /**
     * @param $resolver
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('Default', 'trainee', 'api.profile', 'api.registration')
        ));
    }
}
