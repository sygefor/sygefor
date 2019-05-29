<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 2/13/19
 * Time: 11:09 AM.
 */

namespace AppBundle\Utils;

use Html2Text\Html2Text;
use AppBundle\Entity\Trainer;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Inscription;
use AppBundle\Entity\Organization;
use NotificationBundle\Mailer\Email;
use AppBundle\Entity\Trainee\Trainee;
use Sygefor\Bundle\CoreBundle\Entity\User;
use Egulias\EmailValidator\EmailValidator;
use Gedmo\Exception\UploadableMaxSizeException;
use NotificationBundle\Mailer\Mailer as BaseMailer;
use Egulias\EmailValidator\Validation\RFCValidation;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use NotificationBundle\Mailer\MailerRecipientInterface;
use NotificationBundle\Mailer\MailerRecipientsInterface;
use Sygefor\Bundle\CoreBundle\Entity\Email as EmailEntity;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublipostTemplate;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Sygefor\Bundle\CoreBundle\BatchOperations\Generic\MailingBatchOperation;

/**
 * Class Mailer
 */
class Mailer extends BaseMailer
{
    /** @var ContainerInterface */
    protected $container;

    /** @var array */
    protected $messages;

    /** @var array */
    protected $savedEmails;

    /**
     * Mailer constructor.
     *
     * @param \Swift_Mailer     $mailer
     * @param \Twig_Environment $twig
     * @param array             $configuration
     * @param ContainerInterface
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, array $configuration, ContainerInterface $container)
    {
        $this->messages = [];
        $this->savedEmails = [];
        $this->container = $container;
        parent::__construct($mailer, $twig, $configuration);
    }

    /**
     * @param int   $code
     * @param mixed $recipient
     * @param array $data
     * @param array $excludes
     *
     * @return int
     *
     * @throws \Html2Text\Html2TextException
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function send($code, $recipient, array $data = [], array $excludes = [])
    {
        $emailOrNbrSentEmails = $this->prepareEmail($code, $recipient, $data, $excludes);
        if (is_int($emailOrNbrSentEmails)) {
            return $emailOrNbrSentEmails;
        }

        $email = $this->getRenderedEmail($code, $data);
        if (!$email->isEnabled()) {
            return 0;
        }
        $params = $this->getEmailParams($email, $recipient, $data, $emailOrNbrSentEmails);

        return $this->sendEmail($email, $recipient, $params);
    }

    /**
     * @param $code
     * @param $recipient
     * @param $data
     * @param $excludes
     *
     * @return int
     *
     * @throws \Html2Text\Html2TextException
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function prepareEmail($code, $recipient, &$data, $excludes)
    {
        if ($recipient instanceof MailerRecipientsInterface) {
            $recipient = $recipient->getRecipients();
        }

        if (is_array($recipient) || $recipient instanceof \Traversable) {
            $success = 0;
            foreach ($recipient as $_recipient) {
                $success += $this->send($code, $_recipient, $data, $excludes);
            }

            return $success;
        }

        $emailTo = $recipient;
        if ($recipient instanceof MailerRecipientInterface) {
            if (in_array($recipient, $excludes)) {
                return 0;
            }
            $data['recipient'] = $recipient;
            $emailTo = $recipient->getEmail();
        }

        $emailValidator = new EmailValidator();
        if (!$emailValidator->isValid($emailTo, (new RFCValidation()))) {
            return 0;
        }

        if (in_array($emailTo, $excludes)) {
            return 0;
        }

        return $emailTo;
    }

    /**
     * @param Email  $email
     * @param mixed  $recipient
     * @param array  $data
     * @param string $emailTo
     *
     * @return array
     */
    protected function getEmailParams($email, $recipient, $data, $emailTo)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $env = $this->container->get('kernel')->getEnvironment();

        // Init params
        $fromEmail = $email->getSenderAddress();
        $fromName = $email->getSenderName();
        $replyTo = $email->getSenderAddress();
        $organization = null;
        $attachments = [];
        $send = true;

        // Override initial params with additional params
        if (isset($data['additionalParams'])) {
            $additionalParams = $data['additionalParams'];

            // Override organization
            if (isset($additionalParams['organization'])) {
                $organization = $additionalParams['organization'];
                if (is_int($organization)) {
                    $organization = $em->getRepository(Organization::class)->find($organization);
                }
                $fromName = $organization->getName();
                $replyTo = $organization->getEmail();
            }

            // Override replyTo
            if (isset($additionalParams['replyTo'])) {
                $replyTo = $additionalParams['replyTo'];
            }

            // attach attachements
            $attachments = isset($additionalParams['attachments']) ? $additionalParams['attachments'] : [];
            if (!empty($attachments)) {
                if (!is_array($attachments)) {
                    $attachments = [$attachments];
                }
            }

            // attach publipost templates
            $templateAttachments = isset($additionalParams['templateAttachments']) ? $additionalParams['templateAttachments'] : [];
            $publipostTemplates = [];
            if ($templateAttachments) {
                foreach ($templateAttachments as $templateAttachment) {
                    if (is_int($templateAttachment)) {
                        $publipostTemplates[] = $templateAttachment;
                    } elseif (is_array($templateAttachment) && isset($templateAttachment['id'])) {
                        $publipostTemplates[] = $templateAttachment['id'];
                    }
                }

                $publipostTemplates = $em->getRepository(PublipostTemplate::class)->findBy(array('id' => $publipostTemplates));
                /** @var PublipostTemplate $publipostTemplate */
                foreach ($publipostTemplates as $publipostTemplate) {
                    // find specific publipost service suffix
                    $entityType = $publipostTemplate->getEntity();
                    $entityType = explode('\\', $entityType);
                    $entityType = $entityType[count($entityType) - 1];
                    $serviceSuffix = strtolower($entityType);

                    // call publipost action and generate pdf
                    /** @var MailingBatchOperation $publipostService */
                    $publipostService = $this->container->get('sygefor_core.batch.publipost.'.$serviceSuffix);
                    $publipostIdList = array($recipient->getId());
                    $publipostOptions = array('template' => $publipostTemplate->getId());
                    $file = $publipostService->execute($publipostIdList, $publipostOptions);
                    $fileName = $file['fileUrl'];
                    $fileName = $publipostService->getTempDir().$publipostService->toPdf($fileName);
                    $attachments[] = (new UploadedFile($fileName, $publipostTemplate->getName().'.pdf', 'application/pdf'));
                }
            }

            if (isset($data['additionalParams']) && isset($data['additionalParams']['send'])) {
                $send = $data['additionalParams']['send'];
            }
        }

        if ($this->getUser() && !$organization) {
            $userOrg = $this->getUser()->getOrganization()->getId();
            $organization = $em->getRepository(Organization::class)->find($userOrg);
            $replyTo = $replyTo ?: $organization->getEmail();
        }

        return [
            'fromName' => $fromName,
            'fromEmail' => $fromEmail,
            'replyTo' => $replyTo,
            'To' => $emailTo,
            'CC' => (isset($data['additionalParams']) && isset($data['additionalParams']['CC']) ? $data['additionalParams']['CC'] : []),
            'attachments' => $attachments,
            'send' => $send,
	        'additionalParams' => isset($data['additionalParams']) ? $data['additionalParams'] : []
        ];
    }

    /**
     * @param $email
     * @param $recipient
     * @param $params
     *
     * @return int
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Html2Text\Html2TextException
     */
    protected function sendEmail($email, $recipient, $params)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $body = [
            'FromName' => $params['fromName'],
            'FromEmail' => $params['fromEmail'],
            'Headers' => [
                'Reply-To' => $params['replyTo'],
            ],
            'Subject' => $email->getSubject(),
            'Recipients' => [
                $params['To'],
            ],
            'CC' => $params['CC'],
            'Text-part' => Html2Text::convert($email->getBody()),
            'Html-part' => $email->getBody(),
        ];

        if (count($params['attachments']) > 0) {
            $body['Attachments'] = [];
        }

	    $attachmentsTotalSize = 0;
        /** @var UploadedFile $attachment */
        foreach ($params['attachments'] as $attachment) {
            $fileData = file_get_contents($attachment->getRealPath());
            $fileContent = base64_encode($fileData);
            $body['Attachments'][] = [
                'Content-Type' => $attachment->getClientMimeType(),
                'Filename' => $attachment->getClientOriginalName(),
                'content' => $fileContent,
            ];
	        $attachmentsTotalSize += strlen(serialize($fileContent));
        }

	    if ($attachmentsTotalSize > 10000000 /* 12Mo */) {
		    throw new UploadableMaxSizeException('The total size of attached files has to be over 12Mo.');
	    }

	    return $this->sendViaSMTP($recipient, $email, $body, $params['attachments'], $em, $params);
    }

	/**
	 * @param MailerRecipientInterface $recipient
	 * @param Email                    $email
	 * @param array                    $body
	 * @param array                    $attachments
	 * @param EntityManager            $em
	 * @param array                    $params
	 *
	 * @return int
	 *
	 * @throws \Exception
	 */
	protected function sendViaSMTP($recipient, $email, $body, $attachments, $em, $params)
	{
		$message = \Swift_Message::newInstance($body['Subject'], $body['Html-part'], "text/html", null);
		$message->setFrom($body['FromEmail'], $body['FromName']);
		$message->setTo($body['Recipients'][0]);
		$message->setReplyTo($body['Headers']['Reply-To']);
		$message->addPart($body['Text-part'], 'text/plain');

		foreach ($body['CC'] as $cc) {
			$message->addCc($cc['To'], $cc['Name']);
		}

		if (count($attachments) > 0) {
			if (!is_array($attachments)) {
				$attachments = array($attachments);
			}
			foreach ($attachments as $attachment) {
				if (!$attachment instanceof \Swift_Attachment) {
					$message->attach(new \Swift_Attachment(
						file_get_contents($attachment),
						(method_exists($attachment, 'getClientOriginalName')) ? $attachment->getClientOriginalName() : $attachment->getFileName())
					);
				}
				else {
					$message->attach($attachment);
				}
			}
		}

		$ret = $this->container->get('mailer')->send($message);
		if ($ret && $this->doStoreEmail($params)) {
			$this->saveEmail($recipient, $email, $em);
			$em->flush();
		}

		return $ret;
	}

    /**
     * @param MailerRecipientInterface $recipient
     * @param Email                    $email
     * @param EntityManager            $em
     *
     * @throws \Exception
     */
    protected function saveEmail($recipient, $email, $em)
    {
    	if (!is_object($recipient)) {
    		return;
	    }
        $emailEntity = new EmailEntity();
        $emailEntity->setUserFrom($this->getUser());
        $emailEntity->setEmailFrom($email->getSenderAddress());
        $emailEntity->setPopulate(($email->hasAdditionalParam('populate') ? $email->getAdditionalParam('populate') : true));
        if (get_class($recipient) === Trainee::class ||
            get_class($recipient) === 'Proxies\__CG__\AppBundle\Entity\Trainee\Trainee') {
            $emailEntity->setTrainee($recipient);
        }
        else if (get_class($recipient) === Trainer::class ||
            get_class($recipient) === 'Proxies\__CG__\Sygefor\AppBundle\Entity\Trainer') {
            $emailEntity->setTrainer($recipient);
        }
        else if (get_class($recipient) === Inscription::class ||
            get_class($recipient) === 'Proxies\__CG__\AppBundle\Entity\Inscription') {
            $emailEntity->setTrainee($recipient->getTrainee());
            $emailEntity->setSession($recipient->getSession());
        }
        $emailEntity->setSubject($email->getSubject());
        $emailEntity->setBody($email->getBody());
        $emailEntity->setSendAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $em->persist($emailEntity);
        $this->savedEmails[$recipient->getEmail()] = $emailEntity;
    }

    /**
     * @return User
     */
    protected function getUser()
    {
    	$token = $this->container->get('security.token_storage')->getToken();
    	if (!$token || is_string($token->getUser())) {
    		return null;
	    }

        return $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class)->find($token->getUser()->getId());
    }

    /**
     * @param string $code
     *
     * @return Email
     */
    protected function getEmailFromConfiguration($code)
    {
        if (!isset($this->configuration[$code])) {
            throw new InvalidArgumentException(sprintf('Email with code "%s" does not exist!', $code));
        }

        $configuration = $this->configuration[$code];

        $email = new Email();
        $email->setSenderAddress($this->senderAddress);
        $email->setSenderName($this->senderName);
        $email->setCode($code);
        $email->setTemplate($configuration['template']);
        $email->setAdditionalParams(isset($configuration['additionalParams']) ? $configuration['additionalParams'] : array());

        if (isset($configuration['subject'])) {
            $email->setSubject($configuration['subject']);
        }

        if (isset($configuration['enabled']) && false === $configuration['enabled']) {
            $email->setEnabled(false);
        }
        if (isset($configuration['sender']['name'])) {
            $email->setSenderName($configuration['sender']['name']);
        }
        if (isset($configuration['sender']['address'])) {
            $email->setSenderAddress($configuration['sender']['address']);
        }

        return $email;
    }

	/**
	 * @param $params
	 *
	 * @return bool
	 */
    protected function doStoreEmail($params)
    {
	    if (is_array($params) && isset($params['additionalParams']) && isset($params['additionalParams']['storeEmail'])) {
		    return $params['additionalParams']['storeEmail'];
	    }

	    return true;
    }
}
