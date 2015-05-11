<?php
namespace Sygefor\Bundle\TraineeBundle\BatchOperations;

use Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeDuplicate;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;

class TraineeMergeBatchOperation extends AbstractBatchOperation
{

    /** @var  ContainerBuilder $container */
    private $container;

    protected $targetClass = 'SygeforTraineeBundle:Trainee';

    /** @var  Logger */
    protected $logger;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

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
        try  {
            /** @var Trainee[] $trainees */
            $trainees = $this->getObjectList($idList);

            /** @var SecurityContext $securityContext */
            $securityContext = $this->container->get('security.context');
            $em = $this->container->get('doctrine.orm.entity_manager');

            /** @var Trainee $traineeToKeep */
            $traineeToKeep = $em->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Trainee')->find($options['traineeToKeep']) ;

            /** @var Inscription[] $traineeToKeepInscriptions */
            $traineeToKeepInscriptions = $traineeToKeep->getInscriptions();
            $traineeToKeep->setInscriptions(new ArrayCollection()); // IMPORTANT : detach all inscriptions from trainee

            $this->logger->info("TRAINEE MERGE");
            $this->logger->info("User : " . $securityContext->getToken()->getUser() );
            $this->logger->info("Organization : " . $securityContext->getToken()->getUser()->getOrganization() );
            $this->logger->info("TraineeToKeep : " . $traineeToKeep->getId() . " - " . $traineeToKeep->getFullName());
            $this->logger->info("Trainees : " . join(", ", array_map(function($trainee) { return $trainee->getId() . " - " . $trainee->getFullName(); }, $trainees)) );

            if(!$securityContext->isGranted('EDIT', $traineeToKeep)){
                throw new AccessDeniedException("Vous n'avez pas le droit de modifier cet individu : " . $traineeToKeep->getFullName() . ".");
            }

            $traineeToKeepDuplicates = $this->findAllNewDuplicates($traineeToKeep, $trainees, $em, $securityContext);

            // update data
            foreach ($trainees as $trainee) {

                // check the rights for each trainee
                if(!$securityContext->isGranted('DELETE', $trainee)){
                    throw new AccessDeniedException("Vous n'avez pas le droit de supprimer cet individu : " . $trainee->getFullName() . ".");
                }

                // setting the keeped trainee for inscriptions
                /** @var Inscription[] $traineeInscriptions */
                $traineeInscriptions = $trainee->getInscriptions();
                $trainee->setEmail(uniqid()."@urfist-fake.com");
                $trainee->setInscriptions(new ArrayCollection()); // IMPORTANT : detach all inscriptions from trainee
                $trainee->clearDuplicates();

                $this->logger->info("Inscriptions (".$trainee->getId().") : " . join(", ", array_map(function($inscription) { return $inscription->getId(); }, $traineeInscriptions->toArray())) );

                foreach ($traineeInscriptions as $inscription) {

                    // check if $inscription has a session already used in one $traineeToKeepInscriptions's sessions
                    $idToCheck = $inscription->getSession()->getId();
                    $canBeAdd = true;

                    foreach ($traineeToKeepInscriptions as $traineeToKeepInscription) {
                        if ($traineeToKeepInscription->getSession()->getId() == $idToCheck){
                            $this->logger->info("Duplicate session (".$trainee->getId().") : $idToCheck");

                            // default we dont keep the $insciption
                            $canBeAdd = false;
                            if(!TraineeMergeBatchOperation::isInscriptionAccepted($traineeToKeepInscription) && TraineeMergeBatchOperation::isInscriptionAccepted($inscription)){
//                            // the $inscription is ACCEPTED and not the one already linked to the traineeToKeep
                                $traineeToKeepInscriptions->removeElement($traineeToKeepInscription);
                                $this->logger->info("Inscription from TraineeToKeep removed : " . $traineeToKeepInscription->getId());
                                $traineeToKeepInscription->setTrainee($trainee);
                                $em->persist($traineeToKeepInscription);
                                $em->remove($traineeToKeepInscription);

                                $canBeAdd = true;
                            }
                            break;
                        }
                    }

                    if ($canBeAdd) {
                        $this->logger->info("Inscription transfered : " . $inscription->getId());
                        $traineeToKeepInscriptions->add($inscription);
                    } else{
                        $this->logger->info("Inscription deleted : " . $inscription->getId());
                        $em->remove($inscription);
                    }
                }
                $em->persist($trainee);
            }
            $em->persist($traineeToKeep);

            $em->flush();

            foreach ($traineeToKeepInscriptions as $traineeToKeepInscription) {
                $traineeToKeepInscription->setTrainee($traineeToKeep);
                $em->persist($traineeToKeepInscription);
            }
            $traineeToKeep->setDuplicates($traineeToKeepDuplicates);
            $traineeToKeep->setInscriptions($traineeToKeepInscriptions);

            // set the new email
            if(!empty($options['email'])) {
                $traineeToKeep->setEmail($options['email']);
            }

            // persist
            $em->persist($traineeToKeep);

            foreach ($trainees as $trainee) {
                $em->remove($trainee);
            }

            // flush
            $em->flush();

            // force refresh
            $this->container->get('fos_elastica.index')->refresh();

            return new Response(null, 204);
        } catch(\Exception $e) {
            // log error in logger
            $this->logger->error(get_class($e));
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $options
     * @return array
     */
    public function getModalConfig($options)
    {
        return array('templates' => null);
    }

    /**
     * @param Inscription $inscription
     * @return boolean
     */
    protected static function isInscriptionAccepted($inscription)
    {
        return $inscription->getInscriptionStatus()->getStatus() == InscriptionStatus::STATUS_ACCEPTED;
    }

    /**
     * @param Trainee $traineeToKeep
     * @param $trainees
     */
    protected function findAllNewDuplicates($traineeToKeep, $trainees, $em, $securityContext)
    {
        $traineeToKeepTraineeDuplicates = clone $traineeToKeep->getAllDuplicates();
        // All trainees and traineeToKeep duplicates
        $allTraineeDuplicates = new ArrayCollection();
        $arrayTraineeId = array();
        foreach ($trainees as $trainee) {
            // check the rights for each trainee
            if(!$securityContext->isGranted('DELETE', $trainee)){
                throw new AccessDeniedException("Vous n'avez pas le droit de supprimer cet individu : " . $trainee->getFullName() . ".");
            }
            $arrayTraineeId[] = $trainee->getId();
            $traineeDuplicates = clone $trainee->getAllDuplicates();
            $allTraineeDuplicates = new ArrayCollection(array_merge($allTraineeDuplicates->toArray(), $traineeDuplicates->toArray()));
        }
        $allTraineeDuplicates = new ArrayCollection(array_merge($allTraineeDuplicates->toArray(), $traineeToKeepTraineeDuplicates->toArray()));

        // ArrayCollection vide qui va être rempli de duplicatas au fur à mesure du traitement
        $traineeDuplicates = new ArrayCollection();
        // Array utilisant les ids des sources et des targets pour savoir si un duplicata a déjà été ajouté, sans passer par l'id des duplicatas
        $arrayAlreadyAdded = array();
        /**
         * @var TraineeDuplicate $duplicate
         */
        foreach ($allTraineeDuplicates as $duplicate) {
            // Supprime les duplicatas entre les stagiaires à supprimer
            if (in_array($duplicate->getTraineeSource()->getId(), $arrayTraineeId) && in_array($duplicate->getTraineeTarget()->getId(), $arrayTraineeId)) {
                $duplicate->setTraineeSource($traineeToKeep);
            }
            // Supprime les duplicatas entre les stagiaire à supprimer et le stagiaire à garder (vérification avec TraineeSource)
            else if ($duplicate->getTraineeSource()->getId() == $traineeToKeep->getId() && in_array($duplicate->getTraineeTarget()->getId(), $arrayTraineeId)) {
                $em->remove($duplicate);
            }
            // Supprime les duplicatas entre les stagiaire à supprimer et le stagiaire à garder (vérification avec TraineeTarget)
            else if ($duplicate->getTraineeTarget()->getId() == $traineeToKeep->getId() && in_array($duplicate->getTraineeSource()->getId(), $arrayTraineeId)) {
                $em->remove($duplicate);
            }
            // Pour les duplicatas vers des stagiaire externes à la liste de stagiaires à fusionner
            else {
                // Si le duplicata provient d'un stagiaire à garder
                if ($duplicate->getTraineeSource()->getId() == $traineeToKeep->getId()) {
                    // Si le duplicata n'a pas déjà été ajouté - sinon doublons de duplicatas
                    if ($traineeToKeep->getId() && !$traineeDuplicates->contains($duplicate) &&
                        (!(isset($arrayAlreadyAdded[$duplicate->getTraineeSource()->getId()]) &&
                                in_array($duplicate->getTraineeTarget()->getId(), $arrayAlreadyAdded[$duplicate->getTraineeSource()->getId()])) ||
                            !isset($arrayAlreadyAdded[$duplicate->getTraineeSource()->getId()]))) {
                        $arrayAlreadyAdded[$duplicate->getTraineeSource()->getId()][] = $duplicate->getTraineeTarget()->getId();
                        $traineeDuplicates->add($duplicate);
                    }
                    // Si déjà ajouté, alors le duplicata est supprimé
                    else {
                        $em->remove($duplicate);
                    }
                }
                // Si le duplicata provient d'un stagiaire à supprimer
                else if (in_array($duplicate->getTraineeSource()->getId(), $arrayTraineeId)) {
                    $targetDuplicates = $duplicate->getTraineeTarget()->getAllDuplicates();
                    $traineeSource = $duplicate->getTraineeSource();
                    // liste des duplicatas dont le stagiaire cible est le stagiaire à fusionner
                    $targetDuplicates = $targetDuplicates->filter(function ($targetDuplicates) use ($traineeSource) {
                        return $targetDuplicates->getTraineeTarget()->getId() == $traineeSource->getId();
                    });

                    /**
                     * @var TraineeDuplicate $targetDuplicate
                     */
                    // on vérifie que le duplicata n'a pas déjà été ajouté à la nouvelle liste des duplicatas du stagiaire à conserver
                    // on modifie le stagiaire cible de ses duplicatas pour lui renseigner le stagiaire à garder
                    // on supprime les doublons de duplicata
                    foreach ($targetDuplicates as $targetDuplicate) {
                        $targetDuplicatesByTraineeToKeep = $targetDuplicates->filter(function ($duplicate) use ($traineeSource) {
                            return $duplicate->getTraineeTarget()->getId() == $traineeSource->getId();
                        });

                        if (count($targetDuplicatesByTraineeToKeep) == 0) {
                            $targetDuplicate->setTraineeTarget($traineeToKeep);
                        }
                        else {
                            $em->remove($targetDuplicate);
                        }
                    }

                    $duplicate->setTraineeSource($traineeToKeep);

                    // Si le duplicata n'a pas déjà été ajouté
                    if (!$traineeDuplicates->contains($duplicate) &&
                        (!(isset($arrayAlreadyAdded[$duplicate->getTraineeSource()->getId()]) &&
                                in_array($duplicate->getTraineeTarget()->getId(), $arrayAlreadyAdded[$duplicate->getTraineeSource()->getId()])) ||
                            !isset($arrayAlreadyAdded[$duplicate->getTraineeSource()->getId()]))) {
                        $arrayAlreadyAdded[$duplicate->getTraineeSource()->getId()][] = $duplicate->getTraineeTarget()->getId();
                        $traineeDuplicates->add($duplicate);
                    }
                    // Si déjà ajouté, alors le duplicata est supprimé
                    else {
                        $em->remove($duplicate);
                    }
                }
            }
        }

        /**
         * @var TraineeDuplicate $duplicate
         */
        foreach ($traineeDuplicates as $duplicate) {
            $traineeSource = $duplicate->getTraineeSource();
            $traineeTarget = $duplicate->getTraineeTarget();
            $targetDuplicates = $duplicate->getTraineeTarget()->getAllDuplicates();
            $targetDuplicatesByCurrentDuplicate = $targetDuplicates->filter(function ($targetDuplicatesByCurrentDuplicate) use ($traineeSource) {
                return $targetDuplicatesByCurrentDuplicate->getTraineeTarget()->getId() == $traineeSource->getId();
            });
            if (count($targetDuplicatesByCurrentDuplicate) == 0) {
                $newDuplicate = new TraineeDuplicate();
                $em->persist($newDuplicate);
                $newDuplicate->setTraineeSource($duplicate->getTraineeTarget());
                $newDuplicate->setTraineeTarget($duplicate->getTraineeSource());
                $newDuplicate->setIgnored($duplicate->isIgnored());
                $newDuplicate->setType($duplicate->getType());
            }
            /**
             * @var TraineeDuplicate $traineeTargetted
             */
            foreach ($traineeDuplicates as $traineeTargetted) {
                if ($traineeTargetted->getTraineeTarget() != $duplicate->getTraineeTarget()) {
                    $newDuplicate = new TraineeDuplicate();
                    $em->persist($newDuplicate);
                    $newDuplicate->setTraineeSource($duplicate->getTraineeTarget());
                    $newDuplicate->setTraineeTarget($traineeTargetted->getTraineeTarget());
                    $newDuplicate->setIgnored($duplicate->isIgnored());
                    $newDuplicate->setType($duplicate->getType());
                }
            }
        }

        return $traineeDuplicates;
    }
}
