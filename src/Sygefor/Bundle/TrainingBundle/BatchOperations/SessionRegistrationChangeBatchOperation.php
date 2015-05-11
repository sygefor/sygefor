<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 23/06/14
 * Time: 10:13
 */

namespace Sygefor\Bundle\TrainingBundle\BatchOperations;


use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class InscriptionStatusChangeBatchOperation
 * @package Sygefor\Bundle\TraineeBundle\BatchOperations
 */
class SessionRegistrationChangeBatchOperation extends AbstractBatchOperation
{
    /** @var  ContainerBuilder $container */
    private $container;

    /**
     * @var string
     */
    protected $targetClass = 'SygeforTrainingBundle:Session';

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
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var Inscription[] $inscriptions */
        $sessions = $this->getObjectList($idList);
        $registration = $options['registration'];
        //changing status
        /** @var Session $session */
        foreach ($sessions as $session) {
            if($this->container->get('security.context')->isGranted('EDIT', $session->getTraining())) {
                $session->setRegistration($registration);
            }
        }
        $em->flush();
    }

}
