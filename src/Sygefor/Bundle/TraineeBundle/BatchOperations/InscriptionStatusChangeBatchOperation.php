<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 23/06/14
 * Time: 10:13
 */

namespace Sygefor\Bundle\TraineeBundle\BatchOperations;


use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class InscriptionStatusChangeBatchOperation
 * @package Sygefor\Bundle\TraineeBundle\BatchOperations
 */
class InscriptionStatusChangeBatchOperation extends AbstractBatchOperation
{
    /** @var  ContainerBuilder $container */
    private $container;

    /**
     * @var string
     */
    protected $targetClass = 'SygeforTraineeBundle:Inscription';

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
        /** @var Inscription[] $inscriptions */
        $inscriptions = $this->getObjectList($idList);
        //sending email

        $em = $this->container->get('doctrine.orm.entity_manager');
        $repoInscriptionStatus = $em->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus') ;
        $repoPresenceStatus = $em->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus') ;

        $inscriptionStatus = (empty($options['inscriptionStatus'])) ? null : $repoInscriptionStatus->find($options['inscriptionStatus']);
        $presenceStatus = (empty($options['presenceStatus'])) ? null : $repoPresenceStatus->find($options['presenceStatus']);


        //changing status
        foreach ($inscriptions as $inscription) {
            if($this->container->get('security.context')->isGranted('EDIT', $inscription)) {
                //setting new inscription status
                if ($inscriptionStatus) {
                    $inscription->setInscriptionStatus($inscriptionStatus);
                }

                if ($presenceStatus) {
                    $inscription->setPresenceStatus($presenceStatus);
                }

                $em->persist($inscription);

                //if asked, a mail sent to user
                if (isset ($options['sendMail']) && ($options['sendMail'] == true)) {
                    //managing attachments
                    $attachments = array();
                    if ( $options['attachmentTemplates'] ) {
                        $repo = $this->container->get('doctrine.orm.entity_manager')->getRepository('SygeforListBundle:Term\PublipostTemplate');
                        foreach ($options['attachmentTemplates'] as $tplId){
                            $tpl = $repo->find($tplId);
                            $attachments[] = $this->container->get('sygefor_list.batch.publipost.inscription')->parseFile($tpl->getFile(), array ( $inscription ), true, $tpl->getFileName(), true);
                        }
                    }

                    //sending with e-mail service
                    $this->container->get('sygefor_list.batch.email')->parseAndSendMail($inscription, $options['subject'], $options['message'], $attachments, (isset ( $options['preview'] )) ? $options['preview'] : false);

                    //removing files
                    /** @var File[] $attachments*/
                    foreach ($attachments as $att) {
                        unlink($att->getPathname());
                    }
                }
            }
        }

        $em->flush();
    }

    /**
     * @param $options
     * @return array
     */
    public function getModalConfig($options)
    {
        $userOrg = $this->container->get('security.context')->getToken()->getUser()->getOrganization();
        $templateTerm = $this->container->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById('sygefor_trainee.vocabulary_email_template');
        $attachmentTerm = $this->container->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById('sygefor_list.publipost_template');
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var EntityRepository $repo */
        $repo = $em->getRepository(get_class($templateTerm)) ;
        $attRepo = $em->getRepository(get_class($attachmentTerm));


        if (!empty($options['inscriptionStatus'])){
            $repoInscriptionStatus = $em->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus') ;
            $inscriptionStatus = $repoInscriptionStatus->findById($options['inscriptionStatus']);
            $findCriteria = array('inscriptionStatus' => $inscriptionStatus) ;

            if ($userOrg) {
                $findCriteria['organization'] = $userOrg;
            }

            $templates = $repo->findBy($findCriteria);
        } else if (!empty($options['presenceStatus'])) {
            $repoInscriptionStatus = $em->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus') ;
            $presenceStatus = $repoInscriptionStatus->findById($options['presenceStatus']);
            $findCriteria = array('presenceStatus' => $presenceStatus) ;

            if ($userOrg) {
                $findCriteria['organization'] = $userOrg;
            }

            $templates = $repo->findBy($findCriteria);
        }else {
            $templates = $repo->findBy(array('inscriptionStatus' => null, 'presenceStatus' => null));
        }

        $attTemplates = $attRepo->findBy(array('organization' => $userOrg ? $userOrg : ''));

        return array('templates' => $templates, 'attachmentTemplates' => $attTemplates);
    }

}