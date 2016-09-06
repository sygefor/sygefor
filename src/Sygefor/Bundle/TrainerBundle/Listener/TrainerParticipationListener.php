<?php
namespace Sygefor\Bundle\TrainerBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TrainerBundle\Entity\Participation;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;

/**
 * This listener sync shared informations between Trainee and Inscription
 *
 * @package Sygefor\Bundle\TrainerBundle\Listener
 */
class TrainerParticipationListener implements EventSubscriber
{

    private $entities = array();

    /**
     * Returns hash of events, that this listener is bound to.
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::postUpdate,
            Events::postFlush
        ];
    }

    /**
     * @param $entity
     * @return bool
     */
    protected function isTrainer($entity)
    {
        return ($entity instanceof Trainer);
    }

    /**
     * @param $entity
     * @return bool
     */
    protected function isParticipation($entity)
    {
        return ($entity instanceof Participation);
    }

    /**
     * When a participation is created, copy organization and is_urfist
     * from the Trainer entity
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if($this->isParticipation($entity)) {
            $entity->setIsUrfist($entity->getTrainer()->getIsUrfist());
            $entity->setOrganization($entity->getTrainer()->getOrganization());
        }
    }

    /**
     * When a trainer is updated, we keep it in mind for an update on postflush event
     * for future sessions
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if($this->isTrainer($entity)) {
            $em = $eventArgs->getEntityManager();
            // get the update field list
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $changes = array_keys($uow->getEntityChangeSet($entity));

            // check any organization or is_urfist field changed
            foreach($changes as $property) {
                if ($property === "isUrfist" || $property === "organization" && !in_array($entity, $this->entities)) {
                    $this->entities[]= $entity;
                    return;
                }
            }
        }
    }

    /**
     * All entities that where stored are updated
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs) {
        $em = $eventArgs->getEntityManager();

        if (!empty($this->entities)) {
            foreach ($this->entities as $entity) {
                // update current inscriptions
                $query = $em
                    ->createQuery('SELECT p FROM SygeforTrainerBundle:Participation p
                                  JOIN p.session s
                                  WHERE p.trainer = :trainer AND s.dateBegin >= CURRENT_TIMESTAMP()')
                    ->setParameter('trainer', $entity);

                foreach ($query->getResult() as $participation) {
                    $participation->setIsUrfist($entity->getIsUrfist());
                    $participation->setOrganization($entity->getOrganization());
                }
            }
            $this->entities = array();
            $em->flush();
        }
    }
}
