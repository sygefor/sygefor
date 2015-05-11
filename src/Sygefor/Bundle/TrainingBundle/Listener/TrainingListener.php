<?php
namespace Sygefor\Bundle\TrainingBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Registry\TrainingTypeRegistry;

/**
 * Populate the Training discriminator map
 * + autoincrement local number
 * @package Sygefor\Bundle\TrainingBundle\Listener
 */
class TrainingListener implements EventSubscriber
{
    protected $registry;

    /**
     * Constructor
     */
    public function __construct(TrainingTypeRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::prePersist
        ];
    }

    /**
     * Populate the Training discriminator map
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        if (null === $classMetadata->reflClass) {
            return;
        }

        if($classMetadata->getName() == 'Sygefor\Bundle\TrainingBundle\Entity\Training') {
            // fill the discriminator map with types from the registry
            $map = array();
            foreach($this->registry->getTypes() as $key => $type) {
                $map[$key] = $type['class'];
            }
            $classMetadata->setDiscriminatorMap($map);
        }
    }

    /**
     * Increment the local training number
     *
     * @param LifecycleEventArgs $eventArgs The event arguments
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $training = $eventArgs->getEntity();
        if($training instanceof Training && !$training->getNumber()) {
            $em = $eventArgs->getEntityManager();
            $query = $em->createQuery('SELECT MAX(t.number) FROM SygeforTrainingBundle:Training t WHERE t.organization = :organization')
              ->setParameter('organization', $training->getOrganization());
            $max = (int)$query->getSingleScalarResult();
            $training->setNumber($max+1);
        }
    }
}
