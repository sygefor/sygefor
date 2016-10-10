<?php

namespace Sygefor\Bundle\TrainingBundle\Form;

use Sygefor\Bundle\CoreBundle\AccessRight\AccessRightRegistry;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class AbstractSingleSessionTrainingType.
 */
class SingleSessionTrainingType extends TrainingType
{
    /**
     * @var string
     */
    protected $sessionClass;

    /** @var  string */
    protected $sessionFormType;

    public function __construct(AccessRightRegistry $registry, $sessionClass)
    {
        $this->sessionClass = $sessionClass;

        /** @var AbstractSession $session */
        $session = new $this->sessionClass();
        $this->sessionFormType = $session::getFormType();

        parent::__construct($registry);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // add session
        $builder
            ->add('session', $this->sessionFormType, array(
                'label'              => 'Session',
                'cascade_validation' => 'true',
                'required'           => true,
            ));

        // add some event listeners
        $builder->get('session')->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onSessionPreSetData'));
        $builder->get('session')->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onSessionPostSubmit'));

        // remove some fields
        $builder->remove('firstSessionPeriodSemester');
        $builder->remove('firstSessionPeriodYear');

        parent::buildForm($builder, $options);
    }

    /**
     * If the session data is null (create form), create a new one, add it to the training
     * and replace the data.
     *
     * @param FormEvent $event
     */
    function onSessionPreSetData(FormEvent $event)
    {
        /** @var AbstractTraining $training */
        $training = $event->getForm()->getParent()->getData();
        $session  = $event->getData();
        if (!$session) {
            /** @var AbstractSession $session */
            $session = new $this->sessionClass();
            $session->setTraining($training);
            $training->addSession($session);
            $event->setData($session);
        }
    }

    /**
     * On session submit :
     * - set the session training
     * - update the firstSessionPeriodSemester & firstSessionPeriodYear.
     *
     * @param FormEvent $event
     */
    function onSessionPostSubmit(FormEvent $event)
    {
        $training = $event->getForm()->getParent()->getData();
        $session  = $event->getData();
        $session->setTraining($training);

        // update the training firstSessionPeriodSemester and firstSessionPeriodYear
        if ($session->getDateBegin()) {
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
            'cascade_validation' => true,
        ));
    }
}
