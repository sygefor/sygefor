<?php
namespace Sygefor\Bundle\TrainingBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Tag;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class AbstractSingleSessionTrainingType
 * @package Sygefor\Bundle\TrainingBundle\Form
 */
class AbstractSingleSessionTrainingType extends AbstractTrainingType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // parent
        parent::buildForm($builder, $options);
        // add session
        $builder->add('session', 'sessiontype', array(
            'required' => true,
            'label' => "Session",
            'cascade_validation' => 'true'
        ));
        // add some event listeners
        $builder->get('session')->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onSessionPreSetData'));
        $builder->get('session')->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onSessionPostSubmit'));
        // remove some fields
        $builder->remove('firstSessionPeriodSemester');
        $builder->remove('firstSessionPeriodYear');
    }

    /**
     * If the session data is null (create form), create a new one, add it to the training
     * and replace the data
     *
     * @param FormEvent $event
     */
    function onSessionPreSetData(FormEvent $event) {
        /** @var Training $training */
        $training = $event->getForm()->getParent()->getData();
        $session = $event->getData();
        if(!$session) {
            $session = new Session();
            $session->setTraining($training);
            $training->addSession($session);
            $event->setData($session);
        }
    }

    /**
     * On session submit :
     * - set the session training
     * - update the firstSessionPeriodSemester & firstSessionPeriodYear
     * @param FormEvent $event
     */
    function onSessionPostSubmit(FormEvent $event) {
        $training = $event->getForm()->getParent()->getData();
        $session = $event->getData();
        $session->setTraining($training);

        // update the training firstSessionPeriodSemester and firstSessionPeriodYear
        if($session->getDateBegin()) {
            list($year, $semester) = SemesteredTraining::getYearAndSemesterFromDate($session->getDateBegin());
            $training->setFirstSessionPeriodSemester($semester);
            $training->setFirstSessionPeriodYear($year);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'cascade_validation' => true
        ));
    }
}
