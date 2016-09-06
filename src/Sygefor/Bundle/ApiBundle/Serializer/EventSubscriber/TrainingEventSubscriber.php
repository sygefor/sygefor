<?php
namespace Sygefor\Bundle\ApiBundle\Serializer\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
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
            array('event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize')
        );
    }

    /**
     * On API pre serialize, remove unwanted sessions from the training
     *
     * @param PreSerializeEvent $event
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $training = $event->getObject();
        if($training instanceof Training && self::isApiGroup($event->getContext())) {
            $sessions = $training->getSessions();
            foreach($sessions as $key => $session) {
                if(!$session->getDisplayOnline()) {
                    unset($sessions[$key]);
                }
            }
            $training->setSessions(new ArrayCollection(array_values($sessions->toArray())));
        }
    }

    /**
     * @param Context $context
     * @return boolean
     */
    static public function isApiGroup(Context $context) {
        $groups = $context->attributes->get('groups');
        foreach($groups->getOrElse(array()) as $group) {
            if($group == 'api' || strpos($group, 'api.') === 0) {
                return true;
            }
        }
        return false;
    }
}
