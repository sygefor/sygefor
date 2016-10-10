<?php

namespace Sygefor\Bundle\TrainingBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;

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
            Events::preRemove,
        );
    }

    /**
     * Increment the local training number.
     *
     * @param LifecycleEventArgs $eventArgs The event arguments
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $session = $eventArgs->getEntity();
        if ($session instanceof AbstractSession) {
            $module = $session->getModule();
            if ($module && (empty($module->getSessions()) || ($module->getSessions()->count() === 1 && $module->getSessions()->get(0)->getId() === $session->getId()))) {
                $em = $eventArgs->getEntityManager();
                $em->remove($module);
            }
        }
    }
}
