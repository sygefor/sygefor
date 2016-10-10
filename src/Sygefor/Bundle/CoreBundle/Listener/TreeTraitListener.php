<?php

namespace Sygefor\Bundle\CoreBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

class TreeTraitListener implements EventSubscriber
{
    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata,
        );
    }

    /**
     * Adds mapping to the publishable and publications.
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isTree($classMetadata)) {
            $this->mapTree($classMetadata);
        }
    }

    /**
     * Checks if entity is a tree.
     *
     * @param ClassMetadata $classMetadata
     *
     * @return bool
     */
    private function isTree(ClassMetadata $classMetadata)
    {
        $traits = $classMetadata->reflClass->getTraits();
        foreach ($traits as $class => $trait) {
            if ($class === 'Sygefor\\Bundle\\CoreBundle\\Entity\\Term\\TreeTrait') {
                return true;
            }
        }
    }

    /**
     * Map the tree entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function mapTree(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('parent')) {
            $classMetadata->mapManyToOne(array(
                'fieldName' => 'parent',
                'targetEntity' => $classMetadata->name,
                'inversedBy' => 'children',
                'joinColumns' => array(array(
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'SET NULL',
                )),
            ));
        }
        if (!$classMetadata->hasAssociation('children')) {
            $classMetadata->mapOneToMany(array(
                'fieldName' => 'children',
                'mappedBy' => 'parent',
                'orderBy' => array('lft' => 'ASC'),
                'targetEntity' => $classMetadata->name,
            ));
        }
        if (!$classMetadata->customRepositoryClassName) {
            $classMetadata->setCustomRepositoryClass('Gedmo\\Tree\\Entity\\Repository\\NestedTreeRepository');
        }
    }
}
