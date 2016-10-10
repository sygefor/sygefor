<?php

namespace Sygefor\Bundle\ApiBundle\Serializer\EventSubscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;

/**
 * Trainee serialization event subscriber.
 */
class TraineeEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.post_serialize', 'method' => 'onPostSerialize'),
        );
    }

    /**
     * On api.profile post serialize, add some data to the trainee.
     *
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $groups  = $event->getContext()->attributes->get('groups');
        $trainee = $event->getObject();
        if($trainee instanceof Trainee && in_array('api.token', (array) $groups->getOrElse(array()), true)) {
            $inscriptions = array();
            /** @var Inscription $inscription */
            foreach($trainee->getInscriptions() as $inscription) {
                $inscriptions[] = array(
                    'id'                => $inscription->getId(),
                    'session'           => $inscription->getSession()->getId(),
                    'inscriptionStatus' => $inscription->getInscriptionStatus()->getId(),
                    'module'            => $inscription->getSession()->getModule() ? $inscription->getSession()->getModule()->getId() : null,
                );
            }
            $event->getVisitor()->addData('registrations', $inscriptions);
        }

    }
}
