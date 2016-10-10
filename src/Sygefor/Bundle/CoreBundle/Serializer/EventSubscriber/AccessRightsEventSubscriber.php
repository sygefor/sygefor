<?php

namespace Sygefor\Bundle\CoreBundle\Serializer\EventSubscriber;

use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Sygefor\Bundle\CoreBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class AccessRightsEventSubscriber.
 */
class AccessRightsEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    protected $securityContext;

    /**
     * {@inheritdoc}
     */
    function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
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
     * If the object is a instance of SerializedAccessRights, add access rights to the
     * serialized object.
     *
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        if (!$this->isApiGroup($event->getContext())) {
            $object = $event->getObject();
            if ($object instanceof SerializedAccessRights) {
                $event->getVisitor()->addData('_accessRights', array(
                    'view' => $this->securityContext->isGranted('VIEW', $object),
                    'edit' => $this->securityContext->isGranted('EDIT', $object),
                    'delete' => $this->securityContext->isGranted('DELETE', $object),
                ));
            }
        }
    }

    /**
     * @param Context $context
     *
     * @return bool
     */
    protected function isApiGroup(Context $context)
    {
        $groups = $context->attributes->get('groups');
        foreach ($groups->getOrElse(array()) as $group) {
            if ($group === 'api' || strpos($group, 'api.') === 0) {
                return true;
            }
        }

        return false;
    }
}
