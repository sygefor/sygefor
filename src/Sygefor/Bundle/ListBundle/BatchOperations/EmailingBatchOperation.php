<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 12/06/14
 * Time: 18:13
 */

namespace Sygefor\Bundle\ListBundle\BatchOperations;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Repository\RepositoryFactory;
use Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation;
use Sygefor\Bundle\ListBundle\humanReadablePropertyAccessor\HumanReadablePropertyAccessor;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

class EmailingBatchOperation extends AbstractBatchOperation
{

    /** @var  ContainerBuilder $container */
    private $container;

    protected $targetClass = 'SygeforTraineeBundle:Trainee';

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $idList
     * @return array
     */
    protected function getObjectList(array $idList = array())
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder()
            ->select('e')
            ->from($this->targetClass, 'e')
            ->where('e.id IN (:ids)')
            ->setParameter('ids',$idList);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $idList
     * @param array $options
     * @return mixed
     */
    public function execute(array $idList = array(), array $options = array())
    {
        //setting alternate targetclass if provided in options
        if (isset($options['targetClass'])) {
            $this->setTargetClass($options['targetClass']);
        }

        $targetEntities = $this->getObjectList($idList);

        if (isset ($options['preview']) && $options['preview']) {
            return $this->parseAndSendMail($targetEntities[0], isset($options['subject']) ? $options['subject'] : '' , isset ( $options['message']) ? $options['message'] : '' , null, $preview = true);
        }

        /** var Trainee[] $users */
        foreach ($targetEntities as $key => $user) {
            if(!$this->container->get('security.context')->isGranted('VIEW', $user)) {
                unset($targetEntities[$key]);
            }
        }
        $this->parseAndSendMail($targetEntities,  isset($options['subject']) ? $options['subject'] : '', isset ( $options['message']) ? $options['message'] : '' , (isset($options['attachment'])) ? $options['attachment'] : null );

        return new Response('', 204);
    }

    /**
     * @return array configuration element for front-end modal window
     */
    public function getModalConfig()
    {
        $templateTerm = $this->container->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById('sygefor_trainee.vocabulary_email_template');
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(get_class($templateTerm)) ;

        if (!empty($options['inscriptionStatus'])){
            $repoInscriptionStatus = $em->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus');
            $inscriptionStatus = $repoInscriptionStatus->findById($options['inscriptionStatus']);
            $templates = $repo->findBy(array('inscriptionStatus' => $inscriptionStatus, 'organization' => $this->container->get('security.context')->getToken()->getUser()->getOrganization()));
        } else if (!empty($options['presenceStatus'])) {
            $repoPresenceStatus = $em->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus');
            $presenceStatus = $repoPresenceStatus->findById($options['presenceStatus']);
            $templates = $repo->findBy(array('presenceStatus' => $presenceStatus, 'organization' => $this->container->get('security.context')->getToken()->getUser()->getOrganization()));
        }else {
            //if no presence/inscription status is found, we get all organization templates
            $templates = $repo->findBy(array('organization' => $this->container->get('security.context')->getToken()->getUser()->getOrganization(), 'presenceStatus' => null, 'inscriptionStatus' => null));
        }

        return array('templates' => $templates);
    }


    /**
     * @param $entities
     * @param $subject
     * @param $body
     * @param UploadedFile $attachment
     * @param bool $preview
     * @return array
     */
    public function parseAndSendMail($entities, $subject, $body, $attachments = array(), $preview = false)
    {
        if(!is_array($entities)) {
            $entities = array($entities);
        }

        if ($preview) {
            return array( 'email' => array (
                'subject' => $this->replaceTokens($subject, $entities[0]),
                'message' => $this->replaceTokens($body, $entities[0])
            ));
        } else {
            $message = \Swift_Message::newInstance();
            $organization = $this->container->get('security.context')->getToken()->getUser()->getOrganization();

            $message->setFrom($this->container->getParameter('mailer_from'), $organization->getName());
            $message->setReplyTo($organization->getEmail());

            // attachements
            if (!empty($attachments)) {
                if (!is_array($attachments)) {
                    $attachments = array($attachments);
                }
                foreach ($attachments as $attachment) {
                    $attached = new \Swift_Attachment(file_get_contents($attachment), (method_exists($attachment, 'getClientOriginalName')) ? $attachment->getClientOriginalName() : $attachment->getFileName());
                    $message->attach($attached);
                }
            }

            // foreach entity
            foreach($entities as $entity) {
                try {
                    $hrpa = $this->container->get('sygefor_list.human_readable_property_accessor_factory')->getAccessor($entity);
                    $email = $hrpa->email;
                    $message->setTo($email);
                    $message->setSubject($this->replaceTokens($subject, $entity));
                    $message->setBody($this->replaceTokens($body, $entity));
                    $last = $this->container->get('mailer')->send($message);
                } catch(\Swift_RfcComplianceException $e) {
                    // continue
                }
            }

            return $last;
        }
    }

    /**
     * @param $content
     * @param $entity
     * @return string
     */
    private function replaceTokens($content, $entity)
    {
        /** @var HumanReadablePropertyAccessor $HRPA */
        $HRPA = $this->container->get('sygefor_list.human_readable_property_accessor_factory')->getAccessor($entity);

        $newContent = preg_replace_callback('/\[(.*?)\]/',
            function ($matches) use ($HRPA) {
                $property = $matches[1];

                return $HRPA->$property;
            },
            $content);

        return $newContent;
    }

}
