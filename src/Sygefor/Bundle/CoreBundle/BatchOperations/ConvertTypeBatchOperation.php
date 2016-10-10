<?php

namespace Sygefor\Bundle\CoreBundle\BatchOperations;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Elastica\Bulk;
use Elastica\Bulk\Action;
use Elastica\Client;
use Elastica\Filter\Terms;
use Elastica\Type;
use FOS\ElasticaBundle\Elastica\Index;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\CoreBundle\BatchOperation\AbstractBatchOperation;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;
use Sygefor\Bundle\TrainingBundle\Registry\TrainingTypeRegistry;
use Symfony\Component\Security\Core\SecurityContext;

class ConvertTypeBatchOperation extends AbstractBatchOperation
{
    /** @var EntityManager $securityContext */
    protected $securityContext;

    /** @var TrainingTypeRegistry $trainingTypeRegistry | get new entity type class */
    protected $trainingTypeRegistry;

    /** @var array $correspondanceBetweenTrainings */
    protected $correspondanceBetweenTrainings = array();

    /** @var array $clonedTrainingNumbers */
    protected $clonedTrainingNumbers = array();

    /** @var SearchService $semesteredTrainingSearch */
    protected $semesteredTrainingSearch;

    /** @var Client $elasticaClient */
    protected $elasticaClient;

    /** @var Index $elasticaIndex */
    protected $elasticaIndex;

    /** @var Type $semesteredTrainingType */
    protected $semesteredTrainingType;

    /**
     * ConvertTypeBatchOperation constructor.
     *
     * @param SecurityContext      $securityContext
     * @param TrainingTypeRegistry $trainingTypeRegistry
     * @param SearchService        $semesteredTrainingSearch
     * @param Client               $elasticaClient
     * @param Index                $elasticaIndex
     * @param Type                 $semesteredTrainingType
     */
    public function __construct(SecurityContext $securityContext, TrainingTypeRegistry $trainingTypeRegistry,
                                SearchService $semesteredTrainingSearch, Client $elasticaClient, Index $elasticaIndex, Type $semesteredTrainingType)
    {
        $this->securityContext          = $securityContext;
        $this->trainingTypeRegistry     = $trainingTypeRegistry;
        $this->semesteredTrainingSearch = $semesteredTrainingSearch;
        $this->elasticaClient           = $elasticaClient;
        $this->elasticaIndex            = $elasticaIndex;
        $this->semesteredTrainingType   = $semesteredTrainingType;
    }

    /**
     * @param array $idList
     * @param array $options
     *
     * @return mixed
     */
    public function execute(array $idList = array(), array $options = array())
    {
        $type = $options[0]['type'];
        // get trainings from semestered trainings and verify if there are not several times the same training
        // not transform same training type and meetings
        $entities = SemesteredTraining::getTrainingsByIds($idList, $this->em, array($type, 'meeting'));

        // first create new entities and get old entity sessions
        foreach ($entities as $key => $entity) {
            if ($this->securityContext->isGranted('EDIT', $entity)) {
                // create new entity and copy common old entity properties
                $this->createAndCopyEntity($entity, $type, $this->em, $key);

                // transfer sessions to new training
                $clonedTrainingSessions = new ArrayCollection();
                foreach ($entity->getSessions() as $session) {
                    $session->setTraining($this->correspondanceBetweenTrainings[$entity->getId()]);
                    $clonedTrainingSessions->add($session);
                }
                // remove sessions for old entity
                $entitySessions = new ArrayCollection();
                $entity->setSessions($entitySessions);

                // set sessions to new entity
                $this->correspondanceBetweenTrainings[$entity->getId()]->setSessions($clonedTrainingSessions);
            }
        }
        $this->em->flush();

        // then remove old entities
        $entityRemovedIds = array();
        foreach ($entities as $entity) {
            if ($this->securityContext->isGranted('EDIT', $entity)) {
                $entityRemovedIds[] = $entity->getId();
                $this->em->remove($entity);
            }
        }
        $this->em->flush();
        // then reattributes old entities number to new ones
        foreach ($this->clonedTrainingNumbers as $values) {
            $values['entity']->setNumber($values['number']);
        }
        $this->em->flush();
        $this->elasticaIndex->refresh();

        // remove cascade semestered training
        // some of them are not found by elastica because the semestered training could not have the same id because of session moved from old trainings to new one
        if ( ! empty($entityRemovedIds)) {
            // search wrong existing documents
            $trainingIdFilter = new Terms('training.id', $entityRemovedIds);
            $this->semesteredTrainingSearch->addFilter('training.id', $trainingIdFilter);
            $this->semesteredTrainingSearch->setSize(9999);
            $result = $this->semesteredTrainingSearch->search();

            // delete them
            if ( ! empty($result['items'])) {
                $bulk = new Bulk($this->elasticaClient);
                $bulk->setIndex($this->elasticaIndex);
                $bulk->setType($this->semesteredTrainingType);
                foreach ($result['items'] as $semesteredTraining) {
                    $action = new Action(Action::OP_TYPE_DELETE);
                    $action->setId($semesteredTraining['id']);
                    $bulk->addAction($action);
                }

                return $bulk->send();
            }
        }
        $this->elasticaIndex->refresh();
    }

    /**
     * @param Training      $training
     * @param string        $type
     * @param EntityManager $em
     * @param int           $key
     */
    protected function createAndCopyEntity(Training $training, $type, EntityManager $em, $key)
    {
        // get database max number for organization
        $query = $em->createQuery('SELECT MAX(t.number) FROM SygeforTrainingBundle:Training t WHERE t.organization = :organization')
            ->setParameter('organization', $training->getOrganization());
        $max = (int) $query->getSingleScalarResult();

        // create and copy
        $typeClass = $this->trainingTypeRegistry->getType($type);
        /** @var Training $cloned */
        $cloned = new $typeClass['class']();
        $cloned->copyProperties($training);

        // set max number + entity array key because max number is always the same till we do not flush
        $cloned->setNumber($max + $key + 1);
        $em->persist($cloned);

        // copy array collection elements
        $this->mergeArrayCollectionsAndFlush($cloned, $training);

        // some flags for following operations
        $this->correspondanceBetweenTrainings[$training->getId()] = $cloned;
        $this->clonedTrainingNumbers[]                            = array('entity' => $cloned, 'number' => $training->getNumber());
    }

    /**
     * @param Training $dest
     * @param Training $source
     */
    protected function mergeArrayCollectionsAndFlush($dest, $source)
    {
        // clone common arrayCollections
        if (method_exists($source, 'getAreas')) {
            $dest->duplicateArrayCollection('addArea', $source->getAreas());
        }

        // clone duplicate materials
        $tmpMaterials = $source->getMaterials();
        if ( ! empty($tmpMaterials)) {
            foreach ($tmpMaterials as $material) {
                $newMat = clone $material;
                $dest->addMaterial($newMat);
            }
        }
    }
}
