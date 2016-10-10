<?php

namespace Sygefor\Bundle\FrontBundle\Form;


use Sygefor\Bundle\MyCompanybundle\Entity\Inscription;
use Sygefor\Bundle\CoreBundle\Form\Type\EntityHiddenType;
use Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/16/16
 * Time: 5:28 PM
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
                'class' => AbstractTrainee::class
            ))
            ->add('session', EntityHiddenType::class, array(
                'label' => 'Session',
                'class' => AbstractSession::class
            ))
            ->add('motivation', TextareaType::class, array(
                'label' => 'Motivation',
                'attr' => array('placeholder' => 'Expliquez les raisons pour lesquelles vous souhaitez vous inscrire à cette sesssion.')
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