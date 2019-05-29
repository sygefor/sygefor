<?php

namespace AppBundle\EventListener\ORM;

use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This listener :
 *  - manipulate metadata
 *  - encode and save the password if a new plain password has been set
 *  - generate new password and send credentials to the trainee if the property sendCredentialsEmail has been set to true.
 */
class AccountListener implements EventSubscriber
{
    protected $container;

    /**
     * @param ContainerInterface $container
     */
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
          Events::preUpdate,
          Events::postPersist,
          Events::postUpdate,
        );
    }

    /**
     * preProcess
     * Encode the new password.
     */
    public function preProcess($trainee, $new = false)
    {
        if ($trainee instanceof AbstractTrainee && $trainee->getPlainPassword()) {
            $factory = $this->container->get('security.encoder_factory');
            $encoder = $factory->getEncoder($trainee);
            $trainee->setPassword($encoder->encodePassword($trainee->getPlainPassword(), $trainee->getSalt()));
        }
    }

    /**
     * @param $trainee
     * @param bool $new
     *
     * postProcess
     * Send credentials to the trainee
     */
    public function postProcess($trainee, $new = false)
    {
        if (get_parent_class($trainee) == AbstractTrainee::class) {
            // send some mails to the trainee
            if ($trainee->isSendCredentialsMail()) {
                $this->sendCredentialsMail($trainee, $new);
            }
            if ($trainee->getSendActivationMail()) {
                $this->sendActivationMail($trainee, $new);
            }
        }
    }

    /**
     * prePersist.
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->preProcess($eventArgs->getEntity(), true);
    }

    /**
     * preUpdate.
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->preProcess($eventArgs->getEntity(), false);
    }

    /**
     * postPersist.
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->postProcess($eventArgs->getEntity(), true);
    }

    /**
     * postUpdate.
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->postProcess($eventArgs->getEntity(), false);
    }

    /**
     * sendMail.
     */
    protected function sendCredentialsMail(AbstractTrainee $trainee, $new)
    {
	    $notification = 'trainee.welcome';
	    if ($trainee->getShibbolethPersistentId()) {
		    $notification = 'trainee.welcome_shibboleth';
	    }
	    if (!$trainee->isArchived()) {
		    $this->container->get('notification.mailer')->send($notification, $trainee, [
			    'trainee' => $trainee,
			    'password' => $trainee->getPlainPassword(),
		    ]);
	    }
	    $trainee->setSendCredentialsMail(false);
    }

    /**
     * sendMail.
     */
    protected function sendActivationMail(AbstractTrainee $trainee, $new)
    {
	    $options = $trainee->getSendActivationMail();

	    // generate token & url
	    $token = hash('sha256', $trainee->getId());
	    $params = array(
		    'id' => $trainee->getId(),
		    'token' => $token,
		    'email' => $trainee->getEmail(),
	    );
	    if (!empty($options['redirect'])) {
		    $params['redirect'] = $options['redirect'];
	    }
	    if (!$trainee->isArchived()) {
		    $this->container->get('notification.mailer')->send('trainee.activation', $trainee, [
			    'url' => $this->container->get('router')->generate('api.account.activate', $params, true),
		    ]);
	    }
	    $trainee->setSendActivationMail(false);
    }
}
