<?php

namespace Sygefor\Bundle\TrainingBundle\Serializer\EventSubscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Exception\InvalidArgumentException;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;

/**
 * Session serialization event subscriber.
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
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.post_serialize', 'method' => 'onPostSerialize'),
        );
    }

    /**
     * On post serialize, add session URL.
     *
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $apiSerialization = false;
        // do not set public and private URLS across the API
        $groups = $event->getContext()->attributes->get('groups');
        $groups = (array) $groups->getOrElse(array());
        foreach  ($groups as $group) {
            if (strstr($group, 'api')) {
                $apiSerialization = true;
                break;
            }
        }

        $session = $event->getObject();
        if ($session instanceof AbstractSession && $session->getTraining() !== null) {
            try {
                $event->getVisitor()->addData('frontUrl', $session->getFrontUrl($this->container->getParameter('front_url'), $apiSerialization));
            }
            catch(InvalidArgumentException $e) {
                // nothing to do
            }
        }
    }
}
