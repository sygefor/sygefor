<?php

namespace Sygefor\Bundle\MyCompanyBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Sygefor\Bundle\MyCompanyBundle\Entity\Inscription;
use Sygefor\Bundle\InscriptionBundle\Listener\TraineeInscriptionListener as BaseTraineeInscriptionListener;

/**
 * This listener sync shared informations between Trainee and Inscription.
 */
class TraineeInscriptionListener extends BaseTraineeInscriptionListener
{
    private $entities = array();

    /**
     * When a inscription is created, copy all the professional situation
     * from the Trainee entity.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        /** @var Inscription $entity */
        $entity = $eventArgs->getEntity();
        if ($this->isInscription($entity)) {
            $entity->copyDisciplinary($entity->getTrainee(), false);
        }
    }

    /**
     * When a trainee is updated, we keep it in mind for an update on postflush event
     * for future sessions.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($this->isTrainee($entity)) {
            $em = $eventArgs->getEntityManager();

            // get the update field list
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $changes = array_keys($uow->getEntityChangeSet($entity));

            // check any professional situation field changed
            foreach($changes as $property) {
                if (property_exists('Sygefor\Bundle\TraineeBundle\Entity\DisciplinaryTrait', $property)) {
                    $this->entities [] = $entity;

                    return;
                }
            }
        }
    }

    /**
     * All entities that where stored are updated.
     *
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        if (!empty($this->entities)) {
            foreach ($this->entities as $entity) {
                // update current inscriptions
                $query = $em
                    ->createQuery('SELECT i FROM SygeforInscriptionBundle:AbstractInscription i JOIN i.session s WHERE i.trainee = :trainee AND s.dateBegin >= CURRENT_TIMESTAMP()')
                    ->setParameter('trainee', $entity);
                /** @var Inscription $inscription */
                foreach ($query->getResult() as $inscription) {
                    $inscription->copyDisciplinary($entity);
                }
            }
            $this->entities = array();
            $em->flush();
        }
    }
}
