<?php

namespace AppBundle\Form\Type\Training;

use AppBundle\Entity\Training\Internship;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class InternshipType.
 */
class InternshipType extends AbstractTrainingType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('publicTypes', null, array(
                'label' => 'Publics prioritaires',
                'required' => false,
            ))
            ->add('prerequisites', null, array(
                'label' => 'Pré-requis',
                'required' => false,
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Internship::class,
        ));
    }
}
