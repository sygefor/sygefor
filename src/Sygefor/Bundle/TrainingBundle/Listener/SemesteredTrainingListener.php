<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 17/04/14
 * Time: 10:06.
 */
namespace Sygefor\Bundle\TrainingBundle\Listener;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Events;
use Elastica\Index;
use Elastica\Query\Match;
use Elastica\Type;
use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;

/**
 * Class SemesteredTrainingListener.
 */
class SemesteredTrainingListener extends Listener
{
    /** @var Type $index */
    private $index;

    /**
     *
     */
    public function __construct(ObjectPersisterInterface $objectPersister, IndexableInterface $indexable, Type $index, array $config = array(), $logger = null)
    {
        $events = array(
            Events::preRemove,
            Events::postPersist,
            Events::postUpdate,
            Events::preFlush,
            Events::postFlush,
        );

        $config = array(
          'identifier' => 'id',
          'indexName'  => 'sygefor3',
          'typeName'   => 'semestered_training',
        );

        $this->index = $index;

        parent::__construct($objectPersister, $events, $indexable, $config);
    }

    /**
     * Provides unified method for retrieving a doctrine object from an EventArgs instance.
     *
     * @param EventArgs $eventArgs
     *
     * @throws \RuntimeException if no valid getter is found.
     *
     * @return object Entity | Document
     */
    private function getDoctrineObject(EventArgs $eventArgs)
    {
        if (method_exists($eventArgs, 'getObject')) {
            return $eventArgs->getObject();
        } elseif (method_exists($eventArgs, 'getEntity')) {
            return $eventArgs->getEntity();
        } elseif (method_exists($eventArgs, 'getDocument')) {
            return $eventArgs->getDocument();
        }

        throw new \RuntimeException('Unable to retrieve object from EventArgs.');
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return $this->events;
    }

    /**
     * @param EventArgs $eventArgs
     */
    public function preRemove(EventArgs $eventArgs)
    {
        $object = $this->getDoctrineObject($eventArgs);

        if (in_array('Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining', class_parents(get_class($object)), true))
        {
            $semTrainings = SemesteredTraining::getSemesteredTrainingsForTraining($object);
            foreach ($semTrainings as $semT) {
                $this->scheduledForDeletion [] = $semT->getId();
            }
            $this->scheduledForDeletion[] = $object->getId() . '_' . $object->getFirstSessionPeriodYear() . '_' . $object->getFirstSessionPeriodSemester();
        }
        else if (get_class($object) === 'Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession')
        {
            $training = $object->getTraining();
            if ($training) {
                // remove all semestered training associated to this training because of root id change possibility
                $semTrainings = SemesteredTraining::getSemesteredTrainingsForTraining($training);
                foreach ($semTrainings as $semT) {
                    $this->scheduledForDeletion [] = $semT->getId();
                }
                $this->scheduledForDeletion[] = $training->getId() . '_' . $training->getFirstSessionPeriodYear() . '_' . $training->getFirstSessionPeriodSemester();
                $this->scheduledForDeletion[] = $training->getId() . '_' . $object->getYear() . '_' . $object->getSemester();

                // we need to update the semesteredTraining its associated with
                $date                        = $object->getDateBegin();
                $training                    = $object->getTraining();
                $year                        = $date->format('Y');
                $semester                    = ($date->format('m') < 6) ? 1 : 2;
                $semesteredTraining          = new SemesteredTraining($year, $semester, $training);
                $this->scheduledForUpdate [] = $semesteredTraining;
            }
        }
    }

    /**
     * @param EventArgs $eventArgs
     */
    public function postPersist(EventArgs $eventArgs)
    {
        $object = $this->getDoctrineObject($eventArgs);

        if (in_array('Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining', class_parents(get_class($object)), true)) {
            $semTrainings                = SemesteredTraining::getSemesteredTrainingsForTraining($object);
            $this->scheduledForInsertion = array_merge($this->scheduledForInsertion, $semTrainings);
        }
        else if (get_class($object) === 'Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession') {
            //building SemesteredTraining object
            $training = $object->getTraining();
            if ($training) {
                /** @var \DateTime $date */
                $date     = $object->getDateBegin();
                $year     = $date->format('Y');
                $semester = ($date->format('m') < 6) ? 1 : 2;

                /** @var SemesteredTraining $semesteredTraining */
                $semesteredTraining = new SemesteredTraining($year, $semester, $training);

                // delete initial semestered training if needed
                $keepInitialSemesteredTraining = false;
                foreach ($training->getSessions() as $session) {
                    if ((int) $session->getSemester() === $training->getFirstSessionPeriodSemester() && (int) $session->getYear() === $training->getFirstSessionPeriodYear()) {
                        $keepInitialSemesteredTraining = true;
                        break;
                    }
                }
                if ( ! $keepInitialSemesteredTraining) {
                    $this->scheduledForDeletion[] = $training->getId() . '_' . $training->getFirstSessionPeriodYear() . '_' . $training->getFirstSessionPeriodSemester();
                }
                $this->scheduledForUpdate[] = $semesteredTraining;
            }
        }
    }

    /**
     * @param EventArgs $eventArgs
     */
    public function postUpdate(EventArgs $eventArgs)
    {
        /** @var AbstractSession $object */
        $object = $this->getDoctrineObject($eventArgs);

        if (in_array('Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining', class_parents(get_class($object)), true)) {
            $semTrainings             = SemesteredTraining::getSemesteredTrainingsForTraining($object);
            $this->scheduledForUpdate = array_merge($this->scheduledForUpdate, $semTrainings);
        }
        else if (get_class($object) === 'Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession') {
            $training = $object->getTraining();
            if ($training) {
                $query = new Match();
                $query->setField('training.id', $training->getId());

                //deleting objects
                $this->index->deleteByQuery($query);

                //inserting new objects
                $semTrainings                = SemesteredTraining::getSemesteredTrainingsForTraining($training);
                $this->scheduledForInsertion = array_merge($this->scheduledForInsertion, $semTrainings);
            }
        }
    }
}
