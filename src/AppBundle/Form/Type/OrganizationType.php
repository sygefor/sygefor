<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractOrganizationType;

class OrganizationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('email', 'email', array(
                'label' => 'Email',
            ))
            ->add('phoneNumber', 'text', array(
                'label' => 'Téléphone',
                'required' => false,
            ))
            ->add('address', 'textarea', array(
                'label' => 'Adresse',
                'required' => false,
            ))
            ->add('zip', 'text', array(
                'label' => 'Code postal',
                'required' => false,
            ))
            ->add('city', 'text', array(
                'label' => 'Ville',
                'required' => false,
            ))
            ->add('website', 'url', array(
                'label' => 'Site internet',
                'required' => false,
            ));
    }

    public function getParent()
    {
        return AbstractOrganizationType::class;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Organization::class,
        ));
    }
}
