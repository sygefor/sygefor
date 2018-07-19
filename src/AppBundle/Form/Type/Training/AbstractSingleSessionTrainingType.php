<?php

namespace AppBundle\Form\Type\Training;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AppBundle\Entity\Session\Session;
use AppBundle\Form\Type\Session\SessionType;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Form\Type\Training\AbstractTrainingType;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;
use Sygefor\Bundle\CoreBundle\Model\SemesteredTraining;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class AbstractSingleSessionTrainingType.
 */
class AbstractSingleSessionTrainingType extends AbstractTrainingType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // add session
        $builder
            ->add('session', SessionType::class, array(
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
        if ( ! $session) {
            $session = new Session();
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
