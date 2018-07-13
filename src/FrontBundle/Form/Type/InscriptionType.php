<?php

namespace FrontBundle\Form\Type;

use AppBundle\Entity\Inscription;
use AppBundle\Entity\Term\Trainee\Typology;
use Sygefor\Bundle\CoreBundle\Form\Type\EntityHiddenType;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee;
use Sygefor\Bundle\CoreBundle\Entity\AbstractSession;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/16/16
 * Time: 5:28 PM.
 */
class InscriptionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('trainee', EntityHiddenType::class, array(
                'label' => 'Stagiaire',
                'class' => AbstractTrainee::class,
            ))
            ->add('session', EntityHiddenType::class, array(
                'label' => 'Session',
                'class' => AbstractSession::class,
            ))
            ->add('motivation', TextareaType::class, array(
                'label' => 'Motivation',
                'attr' => array('placeholder' => 'Expliquez les raisons pour lesquelles vous souhaitez vous inscrire à cette sesssion.'),
            ))
            ->add('typology', EntityType::class, array(
                'label' => 'Typologie',
                'class' => Typology::class,
                'choice_label' => function ($typology) {
                    return $typology->getName().' : '.$typology->getDescription();
                },
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
