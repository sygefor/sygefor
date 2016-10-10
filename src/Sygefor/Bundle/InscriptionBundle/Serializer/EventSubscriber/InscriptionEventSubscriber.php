<?php

namespace Sygefor\Bundle\InscriptionBundle\Serializer\EventSubscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Exception\InvalidArgumentException;
use Sygefor\Bundle\InscriptionBundle\Entity\AbstractInscription;

/**
 * Inscription serialization event subscriber.
 */
class InscriptionEventSubscriber implements EventSubscriberInterface
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
     * On post serialize, add inscription price.
     *
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $inscription = $event->getObject();
        if ($inscription instanceof AbstractInscription) {
            try {

            }
            catch (InvalidArgumentException $e) {
                // nothing to do
            }
        }
    }
}
