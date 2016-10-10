<?php

namespace Sygefor\Bundle\InstitutionBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractCorrespondent;

/**
 * Class CorrespondentType.
 */
class BaseCorrespondentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', EntityType::class, array(
                'class'    => 'Sygefor\Bundle\CoreBundle\Entity\PersonTrait\Term\Title',
                'label'    => 'Civilité',
                'required' => false,
            ))
            ->add('lastName', TextType::class, array(
                'label'    => 'Nom',
                'required' => false,
            ))
            ->add('firstName', TextType::class, array(
                'label'    => 'Prénom',
                'required' => false,
            ));
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => AbstractCorrespondent::class,
            'validation_groups' => array('Correspondent'),
        ));
    }
}
