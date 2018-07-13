<?php

namespace AppBundle\EventListener\ORM;

use AppBundle\Entity\CoordinatesTrait;
use AppBundle\Entity\ProfessionalSituationTrait;
use AppBundle\Entity\Trainee\EmployeeTrait;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use AppBundle\Entity\Inscription;
use AppBundle\Entity\Trainee\Trainee;
use Html2Text\Html2Text;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This listener sync shared informations between Trainee and Inscription.
 */
class TraineeInscriptionListener implements EventSubscriber
{
    private $entities = array();

    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::postUpdate,
            Events::postFlush,
        );
    }

    /**
     * isTrainee.
     */
    protected function isTrainee($entity)
    {
        return $entity instanceof Trainee;
    }

    /**
     * isInscription.
     */
    protected function isInscription($entity)
    {
        return $entity instanceof Inscription;
    }

    /**
     * When a inscription is created, copy all the professional situation
     * from the Trainee entity.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        /** @var Inscription $entity */
        $entity = $eventArgs->getEntity();
        if ($this->isInscription($entity)) {
            $entity->copyCoordinates($entity->getTrainee(), false);
            $entity->copyProfessionalSituation($entity->getTrainee(), false);
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
        /** Trainee $entity */
        if ($this->isTrainee($entity)) {
            $em = $eventArgs->getEntityManager();

            // get the update field list
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $changes = array_keys($uow->getEntityChangeSet($entity));

            // check any professional situation field changed
            foreach ($changes as $property) {
                if (property_exists(ProfessionalSituationTrait::class, $property) || property_exists(CoordinatesTrait::class, $property)) {
                    $this->entities[] = $entity;

                    return;
                }
            }
        }
    }

    /**
     * All entities that where stored are updated.
     *
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        if (!empty($this->entities)) {
            foreach ($this->entities as $entity) {
                // update current inscriptions
                $query = $em
                    ->createQuery('SELECT i FROM AppBundle:Inscription i JOIN i.session s WHERE i.trainee = :trainee AND s.dateBegin >= CURRENT_TIMESTAMP()')
                    ->setParameter('trainee', $entity);
                /** @var Inscription $inscription */
                foreach ($query->getResult() as $inscription) {
                    $inscription->copyProfessionalSituation($entity);
                    $inscription->copyCoordinates($entity);
                }
            }
            $this->entities = array();
            $em->flush();
        }
    }
}
