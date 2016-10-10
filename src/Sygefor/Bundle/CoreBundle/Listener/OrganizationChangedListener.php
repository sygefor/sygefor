<?php

namespace Sygefor\Bundle\CoreBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 */
class OrganizationChangedListener implements EventSubscriber
{
    private $entities = array();

    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postUpdate,
            Events::postFlush,
        );
    }

    /**
     * @param $entity
     *
     * @return bool
     */
    protected function containsOrganization($entity)
    {
        return method_exists($entity, 'getOrganization');
    }

    /**
     * When a entity is updated, we keep it in mind for an update on postflush event if organization has changed
     * for future sessions.
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($this->containsOrganization($entity)) {
            $em = $eventArgs->getEntityManager();
            // get the update field list
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $changes = array_keys($uow->getEntityChangeSet($entity));

            // check any organization or is_organization field changed
            foreach($changes as $property) {
                if ($property === 'organization' && ! in_array($entity, $this->entities, true)) {
                    $this->entities[] = $entity;

                    return;
                }
            }
        }
    }

    /**
     * For all entities with an organization changed, we remove entities related to this organization.
     *
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if ( ! empty($this->entities)) {
            $em               = $eventArgs->getEntityManager();
            $propertyAccessor = new PropertyAccessor();
            foreach ($this->entities as $entity) {
                $metadata = $em->getClassMetadata(get_class($entity));
                // read entity properties metadata to find related object attached to an organization
                foreach ($metadata->associationMappings as $fieldName => $fieldMD) {
                    if ( ! empty($fieldMD['targetEntity']) && ! in_array($fieldName, $this->getExcludedProperties(get_class($entity)), true)) {
                        $propertyMetadata = $em->getClassMetadata($fieldMD['targetEntity']);
                        // we check if property is related to an organization
                        if (isset($propertyMetadata->associationMappings['organization'])) {
                            // get the value
                            $value = $propertyAccessor->getValue($entity, $fieldName);
                            // remove array collection items not related to the new organization
                            if ($value instanceof PersistentCollection) {
                                foreach ($value as $item) {
                                    if (is_object($item) && method_exists($item, 'getOrganization')) {
                                        if ($item->getOrganization() !== null && $item->getOrganization() !== $entity->getOrganization()) {
                                            $value->removeElement($item);
                                        }
                                    }
                                }
                            }
                            // remove properties related to another organization
                            else if (method_exists($value, 'getOrganization')) {
                                if ($value->getOrganization() !== null && $value->getOrganization() !== $entity->getOrganization()) {
                                    $propertyAccessor->setValue($value, 'setOrganization', null);
                                }
                            }
                        }
                    }
                }
            }
            $this->entities = array();
            $em->flush();
        }
    }

    /**
     * Exclude some check because we want to keep them if organization changes.
     *
     * @param $class
     *
     * @return array
     */
    protected function getExcludedProperties($class)
    {
        $excludedProperties = array(
          Trainer::class => array(
              'institution',
              'participations',
          ),
        );

        if (isset($excludedProperties[$class])) {
            return $excludedProperties[$class];
        }

        return array();
    }
}
