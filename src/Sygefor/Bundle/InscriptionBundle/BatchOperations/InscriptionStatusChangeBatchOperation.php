<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 28/04/14
 * Time: 10:41.
 */
namespace Sygefor\Bundle\InscriptionBundle\BatchOperations;

use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\CoreBundle\BatchOperation\AbstractBatchOperation;
use Sygefor\Bundle\CoreBundle\BatchOperation\BatchOperationModalConfigInterface;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\PresenceStatus;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MailingBatchOperation.
 */
class InscriptionStatusChangeBatchOperation extends AbstractBatchOperation implements BatchOperationModalConfigInterface, ContainerAwareInterface
{
    /** @var  ContainerBuilder $container */
    private $container;

    /**
     * @var string
     */
    protected $targetClass = 'SygeforInscriptionBundle:AbstractInscription';

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param array $idList
     * @param array $options
     *
     * @return mixed
     */
    public function execute(array $idList = array(), array $options = array())
    {
        $inscriptions = $this->getObjectList($idList);
        //sending email

        $em                    = $this->container->get('doctrine.orm.entity_manager');
        $repoInscriptionStatus = $em->getRepository('Sygefor\Bundle\InscriptionBundle\Entity\Term\InscriptionStatus');
        $repoPresenceStatus    = $em->getRepository('Sygefor\Bundle\InscriptionBundle\Entity\Term\PresenceStatus');

        $inscriptionStatus = (empty($options['inscriptionStatus'])) ? null : $repoInscriptionStatus->find($options['inscriptionStatus']);
        $presenceStatus    = (empty($options['presenceStatus'])) ? null : $repoPresenceStatus->find($options['presenceStatus']);

        //changing status
        $arrayInscriptionsGranted = array();
        foreach ($inscriptions as $inscription) {
            if ($this->container->get('security.context')->isGranted('EDIT', $inscription)) {
                //setting new inscription status
                if ($inscriptionStatus) {
                    $inscription->setInscriptionStatus($inscriptionStatus);
                }
                else if ($presenceStatus || isset($options['presenceStatus'])) {
                    $inscription->setPresenceStatus($presenceStatus);
                }
                $arrayInscriptionsGranted[] = $inscription;
            }
        }
        $em->flush();

        //if asked, a mail sent to user
        if (isset($options['sendMail']) && ($options['sendMail'] === true) && (count($arrayInscriptionsGranted) > 0)) {
            //managing attachments
            foreach ($arrayInscriptionsGranted as $inscription) {
                $attachments = array();
                if ($options['attachmentTemplates']) {
                    $repo = $this->container->get('doctrine.orm.entity_manager')->getRepository('SygeforCoreBundle:Term\PublipostTemplate');
                    foreach ($options['attachmentTemplates'] as $tplId) {
                        $tpl           = $repo->find($tplId);
                        $attachments[] = $this->container->get('sygefor_core.batch.publipost.inscription')->parseFile($tpl->getFile(), array($inscription), true, $tpl->getFileName(), true);
                    }
                }

                //sending with e-mail service
                $this->container->get('sygefor_core.batch.email')->parseAndSendMail($inscription, $options['subject'], $options['message'], $attachments, (isset($options['preview'])) ? $options['preview'] : false);

                //removing files
                /** @var File[] $attachments */
                foreach ($attachments as $att) {
                    unlink($att->getPathname());
                }
            }
        }
    }

    /**
     * @param $options
     *
     * @return array
     */
    public function getModalConfig($options = array())
    {
        $userOrg        = $this->container->get('security.context')->getToken()->getUser()->getOrganization();
        $templateTerm   = $this->container->get('sygefor_core.vocabulary_registry')->getVocabularyById('sygefor_trainee.vocabulary_email_template');
        $attachmentTerm = $this->container->get('sygefor_core.vocabulary_registry')->getVocabularyById('sygefor_core.vocabulary_publipost_template');

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var EntityRepository $repo */
        $repo    = $em->getRepository(get_class($templateTerm));
        $attRepo = $em->getRepository(get_class($attachmentTerm));

        if (!empty($options['inscriptionStatus'])) {
            $repoInscriptionStatus = $em->getRepository(InscriptionStatus::class);
            $inscriptionStatus     = $repoInscriptionStatus->findById($options['inscriptionStatus']);
            $findCriteria          = array('inscriptionStatus' => $inscriptionStatus);

            if ($userOrg) {
                $findCriteria['organization'] = $userOrg;
            }

            $templates = $repo->findBy($findCriteria);
        }
        else if (!empty($options['presenceStatus'])) {
            $repoInscriptionStatus = $em->getRepository(PresenceStatus::class);
            $presenceStatus        = $repoInscriptionStatus->findById($options['presenceStatus']);
            $findCriteria          = array('presenceStatus' => $presenceStatus);

            if ($userOrg) {
                $findCriteria['organization'] = $userOrg;
            }

            $templates = $repo->findBy($findCriteria);
        }
        else {
            $templates = $repo->findBy(array('inscriptionStatus' => null, 'presenceStatus' => null));
        }

        $attTemplates = $attRepo->findBy(array('organization' => $userOrg ? $userOrg : ''));

        return array('templates' => $templates, 'attachmentTemplates' => $attTemplates);
    }
}
