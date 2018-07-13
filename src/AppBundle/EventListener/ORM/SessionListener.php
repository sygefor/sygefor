<?php

namespace AppBundle\EventListener\ORM;

use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use AppBundle\Entity\Session\Session;
use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Entity\Training\LongTraining;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;

/**
 * Remove empty module when removing a session.
 */
class SessionListener implements EventSubscriber
{
    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postUpdate,
            Events::preRemove,
        );
    }

    /**
     * isSession.
     */
    protected function isSession($entity)
    {
        return $entity instanceof Session;
    }

    /**
     * isTraining.
     */
    protected function isTraining($entity)
    {
        return get_parent_class($entity) === AbstractTraining::class;
    }

    /**
     * When a trainee is updated, we keep it in mind for an update on postflush event
     * for future sessions.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        /** @var AbstractTraining $entity */
        $entity = $eventArgs->getEntity();
        if ($this->isTraining($entity) || $this->isSession($entity)) {
            // get the update field list
            $em = $eventArgs->getEntityManager();
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $changes = array_keys($uow->getEntityChangeSet($entity));

            // remove empty modules
            /** @var Session $entity */
            if ($this->isSession($entity)) {
                if (in_array('module', $changes)) {
                    /** @var LongTraining $training */
                    $training = $entity->getTraining();
                    $modules = $training->getModules();
                    foreach ($modules as $module) {
                        if (count($module->getSessions()) === 0) {
                            $em->remove($module);
                        }
                    }
                    $em->flush();
                }
            }
        }
    }

    /**
     * Increment the local training number.
     *
     * @param LifecycleEventArgs $eventArgs The event arguments
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $session = $eventArgs->getEntity();
        if($session instanceof Session) {
            $module = $session->getModule();
            if ($module && (empty($module->getSessions()) ||
                ($module->getSessions()->count() === 1 &&
                $module->getSessions()->get(0)->getId() === $session->getId()))) {
                $em = $eventArgs->getEntityManager();
                $em->remove($module);
            }
        }
    }
}
