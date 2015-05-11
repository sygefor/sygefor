<?php
namespace Sygefor\Bundle\TraineeBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeRepository;
use Symfony\Component\DependencyInjection\Container;

/**
 * This listener :
 *  - manipulate metadata
 *  - encode and save the password if a new plain password has been set
 *  - generate new password and send credentials to the trainee if the property sendCredentialsEmail has been set to true
 *
 * @package Sygefor\Bundle\TraineeBundle\Listener
 */
class TraineeListener implements EventSubscriber
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
          Events::prePersist,
          Events::preUpdate,
          Events::postPersist,
          Events::postUpdate,
          Events::loadClassMetadata
        ];
    }

    /**
     * preProcess
     * Encode the new password
     */
    public function preProcess($trainee, $new = false) {
        if($trainee instanceof Trainee && $trainee->getPlainPassword()) {
            $factory = $this->container->get('security.encoder_factory');
            $encoder = $factory->getEncoder($trainee);
            $trainee->setPassword($encoder->encodePassword($trainee->getPlainPassword(), $trainee->getSalt()));
        }
    }

    /**
     * postProcess
     * Send credentials to the trainee
     */
    public function postProcess($trainee, $new = false) {
        if($trainee instanceof Trainee && $trainee->getPlainPassword()) {
            // send some mails to the trainee
            if($trainee->isSendCredentialsMail()) {
                $this->sendCredentialsMail($trainee, $new);
            }
            if($trainee->getSendActivationMail()) {
                $this->sendActivationMail($trainee, $new);
            }
        }
    }

    /**
     * prePersist
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->preProcess($eventArgs->getEntity(), true);
    }

    /**
     * preUpdate
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->preProcess($eventArgs->getEntity(), false);
    }

    /**
     * postPersist
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->postProcess($eventArgs->getEntity(), true);
    }

    /**
     * postUpdate
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->postProcess($eventArgs->getEntity(), false);
    }

    /**
     * loadClassMetadata
     * email field is not nullable
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs) {
        $classMetadata = $eventArgs->getClassMetadata();
        $class = $classMetadata->getName();
        if($class == 'Sygefor\Bundle\TraineeBundle\Entity\Trainee') {
            $classMetadata->fieldMappings['email']['nullable'] = false;
        }
    }

    /**
     * sendMail
     */
    protected function sendCredentialsMail(Trainee $trainee, $new)
    {
        // prepare the body
        $parameters = array(
          'trainee' => $trainee,
          'password' => $trainee->getPlainPassword(),
          'new' => $new,
          'url' => $this->container->getParameter('front_url')
        );

        $template = 'welcome.txt.twig';
        if($trainee->getShibbolethPersistentId()) {
            // if shibboleth, send special message
            $template = 'welcome.shibboleth.txt.twig';
        }

        $body = $this->container->get('templating')->render('SygeforTraineeBundle:Trainee:' . $template, $parameters);

        // send the mail
        $message = \Swift_Message::newInstance()
          ->setFrom($this->container->getParameter('mailer_from'), $this->container->getParameter('mailer_from_name'))
          ->setReplyTo($trainee->getOrganization()->getEmail())
          ->setSubject("Bienvenue sur SYGEFOR !")
          ->setTo($trainee->getEmail())
          ->setBody($body)
        ;
        $this->container->get('swiftmailer.mailer.local')->send($message);
        $trainee->setSendCredentialsMail(false);
    }

    /**
     * sendMail
     */
    protected function sendActivationMail(Trainee $trainee, $new)
    {
        $options = $trainee->getSendActivationMail();

        // generate token & url
        $token = hash('sha256', $trainee->getId());
        $params = array(
          'id' => $trainee->getId(),
          'token' => $token,
          'email' => $trainee->getEmail()
        );
        if(!empty($options['redirect'])) {
            $params['redirect'] = $options['redirect'];
        }
        $url = $this->container->get('router')->generate('api.account.activate', $params, true);

        // prepare the body
        $parameters = array(
          'trainee' => $trainee,
          'new' => $new,
          'url' => $url
        );

        // generate body
        $body = $this->container->get('templating')->render('SygeforTraineeBundle:Trainee:activation.txt.twig', $parameters);

        // send the mail
        $message = \Swift_Message::newInstance()
          ->setFrom($this->container->getParameter('mailer_from'), $this->container->getParameter('mailer_from_name'))
          ->setReplyTo($trainee->getOrganization()->getEmail())
          ->setSubject("SYGEFOR : Activation de votre compte")
          ->setTo($trainee->getEmail())
          ->setBody($body)
        ;

        $this->container->get('swiftmailer.mailer.local')->send($message);
        $trainee->setSendActivationMail(false);
    }
}
