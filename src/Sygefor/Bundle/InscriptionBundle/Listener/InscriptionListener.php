<?php

namespace Sygefor\Bundle\InscriptionBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Sygefor\Bundle\InscriptionBundle\Entity\AbstractInscription;
use Sygefor\Bundle\TraineeBundle\Entity\Term\EmailTemplate;
use Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee;
use Symfony\Component\DependencyInjection\Container;

/**
 * Inscription listener to perfom some operation on persist/update
 *  - send a mail to the trainee if the property sendInscriptionStatusMail has been set to true.
 */
class InscriptionListener implements EventSubscriber
{
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
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
          Events::postPersist,
          Events::postUpdate,
          Events::loadClassMetadata,
        );
    }

    /**
     * Send the inscription status mail.
     */
    public function postProcess(LifecycleEventArgs $eventArgs)
    {
        $inscription = $eventArgs->getEntity();
        if ($inscription instanceof AbstractInscription) {
            if ($inscription->isSendInscriptionStatusMail()) {
                $this->sendInscriptionStatusMail($eventArgs);
            }

            // sending mail to organization manager if new inscription status is disclaimer
            $this->sendMailDisclaimerInscriptionStatusMail($eventArgs);
        }
    }

    /**
     * postPersist.
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->postProcess($eventArgs, true);
    }

    /**
     * postUpdate.
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->postProcess($eventArgs, false);
    }

    /**
     * loadClassMetadata
     * email field is not nullable.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $class         = $classMetadata->getName();
        if ($class === AbstractInscription::class || get_parent_class($class) === AbstractInscription::class) {
            $classMetadata->fieldMappings['email']['nullable'] = false;
        }
    }

    /**
     * sendMail.
     */
    protected function sendInscriptionStatusMail(LifecycleEventArgs $eventArgs)
    {
        /** @var AbstractInscription $inscription */
        $inscription = $eventArgs->getEntity();

        // find the first template for the given inscription status
        $repository = $eventArgs->getEntityManager()->getRepository('SygeforTraineeBundle:Term\EmailTemplate');

        /** @var EmailTemplate $template */
        $template = $repository->findOneBy(array(
            'organization'      => $inscription->getSession()->getTraining()->getOrganization(),
            'inscriptionStatus' => $inscription->getInscriptionStatus(),
        ), array('position' => 'ASC'));

        if ($template) {
            // send the mail with the batch service
            $this->container->get('sygefor_core.batch.email')->parseAndSendMail($inscription, $template->getSubject(), $template->getCc(), $template->getBody());
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    protected function sendMailDisclaimerInscriptionStatusMail($eventArgs)
    {
        /** @var AbstractInscription $inscription */
        $inscription = $eventArgs->getEntity();

        $uow    = $eventArgs->getEntityManager()->getUnitOfWork();
        $chgSet = $uow->getEntityChangeSet($inscription);

        if(isset($chgSet['inscriptionStatus'])) {

            $status = $inscription->getInscriptionStatus();

            if ($status->getNotify()) {
                $body = "Bonjour,\n" .
                    "Le status de l'inscription de " . $inscription->getTrainee()->getFullName() . ' à la session du ' . $inscription->getSession()->getDateBegin()->format('d/m/Y') . "\nde la formation intitulée '" . $inscription->getSession()->getTraining()->getName() . "'\n"
                    . "est passé à '" . $status->getName() . "'";

                $message = \Swift_Message::newInstance();
                $message->setFrom($this->container->getParameter('mailer_from'), $inscription->getSession()->getTraining()->getOrganization()->getName());
                $message->setReplyTo($inscription->getSession()->getTraining()->getOrganization()->getEmail());
                $message->setTo($inscription->getSession()->getTraining()->getOrganization()->getEmail());
                $message->setSubject($status->getName());
                $message->setBody($body);

                $this->container->get('mailer')->send($message);
            }
        }
    }

}
