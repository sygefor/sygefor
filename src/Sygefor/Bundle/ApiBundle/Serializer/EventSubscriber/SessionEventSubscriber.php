<?php
namespace Sygefor\Bundle\ApiBundle\Serializer\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
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
     * @inheritdoc
     */
    static public function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize')
        );
    }

    /**
     * On API pre serialize, add allMaterial property
     *
     * @param PreSerializeEvent $event
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $allMaterials = new ArrayCollection();
        /** @var Session $session */
        $session = $event->getObject();
        if($session instanceof Session && TrainingEventSubscriber::isApiGroup($event->getContext())) {
            $training = $session->getTraining();
            foreach($session->getMaterials() as $material) {
                $allMaterials->add($material);
            }
            foreach($training->getMaterials() as $material) {
                $allMaterials->add($material);
            }
            $session->setAllMaterials($allMaterials);
        }
    }
}
