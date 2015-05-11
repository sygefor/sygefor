<?php
namespace Sygefor\Bundle\TraineeBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class InscriptionType
 * @package Sygefor\Bundle\TraineeBundle\Form
 */
class InscriptionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('trainee', 'entity_hidden', array(
            'label' => 'Stagiaire',
            'class' => 'SygeforTraineeBundle:Trainee',
            'invalid_message' => ''
        ));

        $builder->add('session', 'entity_hidden', array(
            'label' => 'Session',
            'class' => 'SygeforTrainingBundle:Session',
            'invalid_message' => 'Session non reconnue'
        ));

        $builder->add('inscriptionStatus', 'entity', array(
            'label' => 'Status d\'inscription',
            'class' => 'SygeforTraineeBundle:Term\InscriptionStatus'
        ));

        $builder->add('presenceStatus', 'entity', array(
            'label' => 'Status de présence',
            'class' => 'SygeforTraineeBundle:Term\PresenceStatus'
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'inscription';
    }

}
