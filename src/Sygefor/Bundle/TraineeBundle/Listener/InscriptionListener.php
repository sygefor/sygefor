<?php
namespace Sygefor\Bundle\TraineeBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Term\EmailTemplate;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeRepository;
use Symfony\Component\DependencyInjection\Container;

/**
 * Inscription listener to perfom some operation on persist/update
 *  - send a mail to the trainee if the property sendInscriptionStatusMail has been set to true
 *
 * @package Sygefor\Bundle\TraineeBundle\Listener
 */
class InscriptionListener implements EventSubscriber
{
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
          Events::postPersist,
          Events::postUpdate,
          Events::loadClassMetadata
        ];
    }

    /**
     * Send the inscription status mail
     */
    public function postProcess(LifecycleEventArgs $eventArgs, $new = false) {
        $inscription = $eventArgs->getEntity();
        if($inscription instanceof Inscription) {
            if($inscription->isSendInscriptionStatusMail()) {
                $this->sendInscriptionStatusMail($eventArgs);
            }
        }
    }

    /**
     * postPersist
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->postProcess($eventArgs, true);
    }

    /**
     * postUpdate
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->postProcess($eventArgs, false);
    }

    /**
     * loadClassMetadata
     * email field is not nullable
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs) {
        $classMetadata = $eventArgs->getClassMetadata();
        $class = $classMetadata->getName();
        if($class == 'Sygefor\Bundle\TraineeBundle\Entity\Inscription') {
            $classMetadata->fieldMappings['email']['nullable'] = false;
        }
    }

    /**
     * sendMail
     */
    protected function sendInscriptionStatusMail(LifecycleEventArgs $eventArgs)
    {
        /** @var Inscription $inscription */
        $inscription = $eventArgs->getEntity();

        // find the first template for the given inscription status
        $repository = $eventArgs->getEntityManager()->getRepository('SygeforTraineeBundle:Term\EmailTemplate');

        /** @var EmailTemplate $template */
        $template = $repository->findOneBy(array(
            'organization' => $inscription->getSession()->getTraining()->getOrganization(),
            'inscriptionStatus' => $inscription->getInscriptionStatus()
        ), array('position' => 'ASC'));

        if($template) {
            // send the mail with the batch service
            $this->container->get('sygefor_list.batch.email')->parseAndSendMail($inscription, $template->getSubject(), $template->getBody());
        }
    }
}
