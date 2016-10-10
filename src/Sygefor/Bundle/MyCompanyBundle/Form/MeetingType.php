<?php

namespace Sygefor\Bundle\MyCompanyBundle\Form;

use Sygefor\Bundle\MyCompanyBundle\Entity\Meeting;
use Sygefor\Bundle\TrainingBundle\Form\SingleSessionTrainingType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MeetingType extends SingleSessionTrainingType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('national', null, array(
                'required' => false,
                'label'    => 'National',
            ));

        parent::buildForm($builder, $options);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => Meeting::class,
        ));
    }
}
