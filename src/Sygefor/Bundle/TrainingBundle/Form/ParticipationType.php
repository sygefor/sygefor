<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 7/5/16
 * Time: 2:39 PM.
 */
namespace Sygefor\Bundle\TrainingBundle\Form;

use Sygefor\Bundle\CoreBundle\Form\Type\EntityHiddenType;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractParticipation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $notBlank = new NotBlank(array('message' => 'Vous devez sélectionner une session.'));
        $notBlank->addImplicitGroupName('session_add');

        $builder
            ->add('trainer', EntityHiddenType::class, array(
                'label'       => 'Formateur',
                'class'       => 'SygeforTrainerBundle:AbstractTrainer',
                'constraints' => new NotBlank(array('message' => 'Vous devez sélectionner un formateur.')),
            ))
            ->add('session', EntityHiddenType::class, array(
                'label'       => 'Session',
                'class'       => 'SygeforTrainingBundle:Session\AbstractSession',
                'constraints' => $notBlank,
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AbstractParticipation::class,
        ));
    }
}
