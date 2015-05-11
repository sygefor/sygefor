<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 17/04/14
 * Time: 10:06
 */

namespace Sygefor\Bundle\TrainingBundle\Listener;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Events;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Match;
use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;
use Elastica\Type;

/**
 * Class SemesteredTrainingListener
 * @package Sygefor\Bundle\TrainingBundle\Listener
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
        $events = array (
            Events::preRemove,
            Events::postPersist,
            Events::postUpdate,
            Events::preFlush,
            Events::postFlush
        );

        $config = array(
          'identifier' => "id",
          'indexName' => 'sygefor3',
          'typeName' => 'semestered_training'
        );

        $this->index = $index;

        parent::__construct($objectPersister, $events, $indexable, $config);
    }

    /**
     * Provides unified method for retrieving a doctrine object from an EventArgs instance
     *
     * @param   EventArgs           $eventArgs
     * @return  object              Entity | Document
     * @throws  \RuntimeException   if no valid getter is found.
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

        if (in_array('Sygefor\Bundle\TrainingBundle\Entity\Training', class_parents(get_class($object))))
        {
            $semTrainings = SemesteredTraining::getSemesteredTrainingsForTraining($object);
            foreach ($semTrainings as $semT) {
                $this->scheduledForDeletion []= $semT->getId() ;
            }

        } else if (get_class($object) == 'Sygefor\Bundle\TrainingBundle\Entity\Session') {
            //Here a session is being removed. We need to update/delete the semesteredTraining its associated with
            /** @var \DateTime $date */
            $date = $object->getDateBegin();

            $training = $object->getTraining();
            if ($training) {
                $year = $date->format('Y');
                $semester = ($date->format('m') < 6) ? 1 : 2;

                /** @var SemesteredTraining $semesteredTraining */
                $semesteredTraining = new SemesteredTraining($year, $semester, $training);
                $sessionsForSemesterdTraining = $semesteredTraining->getSessions();

                //if one (or less) session was present in semestered training, we can remove it
                if ( count ($sessionsForSemesterdTraining) == 0 ) {
                    $this->scheduledForDeletion []= $semesteredTraining->getId();
                } else {//we update session list semestered training and give it to the "to be updated" object list
                    $this->scheduledForUpdate []= $semesteredTraining;
                }
            }
        }
    }

    /**
     * @param EventArgs $eventArgs
     */
    public function postPersist(EventArgs $eventArgs)
    {
        $object = $this->getDoctrineObject($eventArgs);

        if (in_array('Sygefor\Bundle\TrainingBundle\Entity\Training', class_parents(get_class($object)))) {
            $semTrainings = SemesteredTraining::getSemesteredTrainingsForTraining($object);
            $this->scheduledForInsertion = array_merge($this->scheduledForInsertion, $semTrainings);
        } else if (get_class($object) == 'Sygefor\Bundle\TrainingBundle\Entity\Session') {

            //building SemesteredTraining object
            $training = $object->getTraining();
            if ($training) {
                /** @var \DateTime $date */
                $date = $object->getDateBegin();
                $year = $date->format('Y');
                $semester = ($date->format('m') < 6) ? 1 : 2;

                /** @var SemesteredTraining $semesteredTraining */
                $semesteredTraining = new SemesteredTraining($year, $semester, $training);

                $this->scheduledForUpdate[] = $semesteredTraining ;
            }
        }
    }

    /**
     * @param EventArgs $eventArgs
     */
    public function postUpdate(EventArgs $eventArgs)
    {
        /** @var Session $object */
        $object = $this->getDoctrineObject($eventArgs);

        if (in_array('Sygefor\Bundle\TrainingBundle\Entity\Training', class_parents(get_class($object)))) {
            $semTrainings = SemesteredTraining::getSemesteredTrainingsForTraining($object);
            $this->scheduledForUpdate = array_merge($this->scheduledForUpdate, $semTrainings);
        } else if (get_class($object) == 'Sygefor\Bundle\TrainingBundle\Entity\Session') {
            $training = $object->getTraining();
            if ($training) {
                $query = new Match();
                $query->setField('training.id', $training->getId());

                //deleting objects
                $this->index->deleteByQuery($query);

                //inserting new objects
                $semTrainings = SemesteredTraining::getSemesteredTrainingsForTraining($training);
                $this->scheduledForInsertion = array_merge($this->scheduledForInsertion, $semTrainings);
            }
        }
    }
}
