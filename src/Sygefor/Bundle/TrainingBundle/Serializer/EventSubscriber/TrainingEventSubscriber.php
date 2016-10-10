<?php

namespace Sygefor\Bundle\TrainingBundle\Serializer\EventSubscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Exception\InvalidArgumentException;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;

/**
 * Training serialization event subscriber.
 */
class TrainingEventSubscriber implements EventSubscriberInterface
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
     * On post serialize, add type.
     *
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $training = $event->getObject();
        if($training instanceof AbstractTraining) {
            try {
                $event->getVisitor()->addData('type', $training->getType());
            } catch(InvalidArgumentException $e) {
                // nothing to do
            }
        }
    }
}
