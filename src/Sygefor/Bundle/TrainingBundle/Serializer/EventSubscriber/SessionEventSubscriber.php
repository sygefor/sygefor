<?php
namespace Sygefor\Bundle\TrainingBundle\Serializer\EventSubscriber;

use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\Exception\InvalidArgumentException;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Session serialization event subscriber
 *
 * @package Sygefor\Bundle\UserBundle\Listener
 */
class SessionEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var
     */
    protected $container;

    /**
     * @param $container
     */
    function __construct($container)
    {
        $this->container = $container;
    }

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
     * On post serialize, add session URL
     *
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $session = $event->getObject();
        if($session instanceof Session) {
            try {
                $event->getVisitor()->addData('publicUrl', $this->container->getParameter('front_url') . '/training/' . $session->getTraining()->getId() . '/' . $session->getId());
            } catch(InvalidArgumentException $e) {
                // nothing to do
            }
        }
    }
}
