<?php

namespace AppBundle\EventListener\ORM;

use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use AppBundle\Entity\Session\Session;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * Class SessionDayNumberListener.
 */
class SessionDayNumberListener implements EventSubscriber
{
    private $entities = array();

    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::postUpdate,
            Events::postFlush,
        );
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($this->isSession($entity)) {
            $this->entities[] = $entity;
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        /** @var Session $entity */
        $entity = $eventArgs->getEntity();
        if ($this->isSession($entity)) {
            $em = $eventArgs->getEntityManager();

            // get the update field list
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $changes = $uow->getEntityChangeSet($entity);
            if (isset($changes['dayNumber'])) {
                $this->entities[] = $entity;
            }
        }
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        if (!empty($this->entities)) {
            /** @var Session $session */
            foreach ($this->entities as $session) {
                $session->setHourNumber($session->getDayNumber() * 7);
            }
            $this->entities = array();
            $em->flush();
        }
    }

    /**
     * @param $entity
     *
     * @return bool
     */
    protected function isSession($entity)
    {
        return $entity instanceof Session;
    }
}
