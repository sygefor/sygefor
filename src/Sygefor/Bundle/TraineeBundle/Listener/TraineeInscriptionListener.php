<?php
namespace Sygefor\Bundle\TraineeBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;

/**
 * This listener sync shared informations between Trainee and Inscription
 *
 * @package Sygefor\Bundle\TraineeBundle\Listener
 */
class TraineeInscriptionListener implements EventSubscriber
{

    private $entities = array();
    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::postUpdate,
            Events::postFlush
        ];
    }

    /**
     * isTrainee
     */
    protected function isTrainee($entity)
    {
        return ($entity instanceof Trainee);
    }

    /**
     * isInscription
     */
    protected function isInscription($entity)
    {
        return ($entity instanceof Inscription);
    }

    /**
     * When a inscription is created, copy all the professional situation
     * from the Trainee entity
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if($this->isInscription($entity)) {
            $entity->copyProfessionalSituation($entity->getTrainee());
            $entity->copyCoordinates($entity->getTrainee());
        }
    }

    /**
     * When a trainee is updated, we keep it in mind for an update on postflush event
     * for future sessions
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if($this->isTrainee($entity)) {

            $em = $eventArgs->getEntityManager();

            // get the update field list
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $changes = array_keys($uow->getEntityChangeSet($entity));

            // check any professional situation field changed
            foreach($changes as $property) {
                if(property_exists('Sygefor\Bundle\TraineeBundle\Entity\ProfessionalSituationTrait', $property) || property_exists('Sygefor\Bundle\CoreBundle\Entity\CoordinatesTrait', $property)) {

                    $this->entities []= $entity;

                    return;
                }
            }
        }
    }

    /**
     * All entities that where stored are updated
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs) {

        $em = $eventArgs->getEntityManager();

        if (!empty($this->entities)) {
            foreach ($this->entities as $entity) {
                // update current inscriptions
                $query = $em
                    ->createQuery('SELECT i FROM SygeforTraineeBundle:Inscription i JOIN i.session s WHERE i.trainee = :trainee AND s.dateBegin >= CURRENT_TIMESTAMP()')
                    ->setParameter('trainee', $entity);

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
