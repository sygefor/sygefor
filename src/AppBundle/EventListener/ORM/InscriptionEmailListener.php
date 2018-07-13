<?php

namespace AppBundle\EventListener\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use AppBundle\Entity\Inscription;
use Sygefor\Bundle\CoreBundle\Entity\Email;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class InscriptionEmailListener implements EventSubscriber
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
            Events::loadClassMetadata,
            Events::prePersist,
            Events::postUpdate,
            Events::postFlush,
        );
    }

    /**
     * isEmail.
     */
    protected function isEmail($entity)
    {
        return $entity instanceof Email;
    }

    /**
     * loadClassMetadata
     * email field is not nullable.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $class = $classMetadata->getName();
        if ($class === Inscription::class) {
            $classMetadata->fieldMappings['email']['nullable'] = false;
        }
    }

    /**
     * When a inscription is created, copy all the professional situation
     * from the Trainee entity.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($this->isEmail($entity)) {
            $this->setLastDate($eventArgs->getEntityManager(), $entity);
        }
    }

    /**
     * When a trainee is updated, we keep it in mind for an update on postflush event
     * for future sessions.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($this->isEmail($entity)) {
            $this->entities [] = $entity;
        }
    }

    /**
     * All entities that where stored are updated.
     *
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if (!empty($this->entities)) {
            foreach ($this->entities as $entity) {
                // update current inscriptions
                $this->setLastDate($eventArgs->getEntityManager(), $entity);
            }
            $this->entities = array();
            $eventArgs->getEntityManager()->flush();
        }
    }

    /**
     * @param EntityManager $em
     * @param Email         $entity
     */
    protected function setLastDate(EntityManager $em, Email $entity)
    {
        if ($entity->getTrainee() && $entity->getSession()) {
            /** @var Inscription $inscription */
            $inscription = current($em
                ->createQuery('SELECT i FROM AppBundle:Inscription i WHERE i.session = :session AND i.trainee = :trainee')
                ->setParameter('session', $entity->getSession())
                ->setParameter('trainee', $entity->getTrainee())
                ->getResult());
            if ($inscription) {
                $inscription->setLastEmailDate($entity->getSendAt());
            }
        }
    }
}
