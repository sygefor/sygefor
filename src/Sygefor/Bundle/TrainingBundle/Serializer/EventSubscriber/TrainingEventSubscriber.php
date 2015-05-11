<?php
namespace Sygefor\Bundle\TrainingBundle\Serializer\EventSubscriber;

use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\Exception\InvalidArgumentException;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Training serialization event subscriber
 *
 * @package Sygefor\Bundle\UserBundle\Listener
 */
class TrainingEventSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    static public function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.post_serialize', 'method' => 'onPostSerialize')
        );
    }

    /**
     * On post serialize, add type
     *
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $training = $event->getObject();
        if($training instanceof Training) {
            try {
                $event->getVisitor()->addData('type', $training->getType());
            } catch(InvalidArgumentException $e) {
                // nothing to do
            }
        }
    }
}
