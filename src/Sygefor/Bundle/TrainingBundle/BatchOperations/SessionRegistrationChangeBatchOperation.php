<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 23/06/14
 * Time: 10:13.
 */
namespace Sygefor\Bundle\TrainingBundle\BatchOperations;

use Sygefor\Bundle\CoreBundle\BatchOperation\AbstractBatchOperation;
use Sygefor\Bundle\InscriptionBundle\Entity\AbstractInscription;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InscriptionStatusChangeBatchOperation.
 */
class SessionRegistrationChangeBatchOperation extends AbstractBatchOperation
{
    /** @var ContainerBuilder $container */
    private $container;

    /**
     * @var string
     */
    protected $targetClass = 'SygeforTrainingBundle:Session\AbstractSession';

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
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
        $em = $this->container->get('doctrine.orm.entity_manager');
        /* @var AbstractInscription[] $inscriptions */
        $sessions     = $this->getObjectList($idList);
        $registration = $options['registration'];
        //changing status
        /** @var AbstractSession $session */
        foreach ($sessions as $session) {
            if($this->container->get('security.context')->isGranted('EDIT', $session->getTraining())) {
                $session->setRegistration($registration);
            }
        }
        $em->flush();
    }
}
