<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Inscription;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractInscriptionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class InscriptionType.
 */
class InscriptionType extends AbstractInscriptionType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('motivation', TextareaType::class, array(
                'label' => 'Motivation',
                'required' => false,
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Inscription::class,
        ));
    }
}
