<?php

namespace Sygefor\Bundle\TaxonomyBundle\Vocabulary;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class VocabularyRegistry
 * @package Sygefor\Bundle\VocabularyBundle\Vocabulary
 */
class VocabularyRegistry
{
    /**
     * @var array
     */
    private $vocabularies;

    /**
     * @var array
     */
    private $groups;

    /**
     *
     */
    public function __construct()
    {
        $this->vocabularies = array();
        $this->groups = array();
    }

    /**
     * @param VocabularyInterface $vocabulary
     */
    public function addVocabulary(VocabularyInterface $vocabulary, $id, $group = "Misc")
    {
        $vocabulary->setVocabularyId($id);
        $this->vocabularies[$id] = $vocabulary;
        if (empty($this->groups[$group])) {
            $this->groups[$group] = array();
        }
        $this->groups[$group][$id] = $vocabulary;
    }

    /**
     * @param string $id
     * @return VocabularyInterface
     */
    public function getVocabularyById($id)
    {
        return isset($this->vocabularies[$id]) ? $this->vocabularies[$id] : null;
    }

    /**
     * @return array
     */
    public function getVocabularies()
    {
        return $this->vocabularies;
    }

    /**
     * returns known groups
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * counts and returns the number of usages of term among all entities
     * @param ObjectRepository $em
     * @param $vocTerm
     * @return array
     */
    public function getTermUsages(EntityManager $em, $vocTerm, $getCount = true)
    {
        /** @var ObjectRepository $repo */
        $meta = $em->getMetadataFactory()->getAllMetadata();
        $vocClass = get_class($vocTerm);
        $termId = $vocTerm->getId();

        $usages = array();

        $totalCount = 0;

        /** @var ClassMetadata $m */
        foreach ($meta as $m) {
            $mapps = $m->getAssociationMappings();
            foreach ($mapps as $map) {

                if ($vocClass == $map['targetEntity'] && ($map['isOwningSide'])) {

                    if ( $map['type'] == ClassMetadataInfo::MANY_TO_MANY ) {
                        //getting all entities
                        $qb1 = $em->createQueryBuilder();;
                        $qb2 =$em->createQueryBuilder();
                        $qb1->select('f.id')
                            ->from($m->getName(),'f')
                            ->leftJoin('f.'.$map["fieldName"], 'c')
                            ->where( $qb1->expr()->in('c', ':c'))
                            ->setParameter('c', $vocTerm);

                        $qb2->select('t')
                            ->from($m->getName(),'t')
                            ->where( $qb1->expr()->in('t.id', ':ids'))->setParameter('ids',$qb1->getQuery()->getResult());

                        $tmpArray = $qb2->getQuery()->getResult();

                        if (count($tmpArray)) {
                            $totalCount += count($tmpArray) ;
                            $usages[$m->getName()] = array('multiple' => true, 'fieldName' => $map['fieldName'], 'entities' => $tmpArray);
                        }
                    } else {
                        $qb = $em->createQueryBuilder()
                            ->select('t')
                            ->from($m->getName(), 't')
                            ->where('t.' . $map['fieldName'] . '= :id')->setParameter('id', $termId);

                        $tmpArray = $qb->getQuery()->getResult();
                        if (count($tmpArray)) {
                            $totalCount += count($tmpArray) ;
                            $usages[$m->getName()] = array('multiple' => false, 'fieldName' => $map['fieldName'], 'entities' => $tmpArray);
                        }
                    }
                }
            }
        }


        if ($getCount) {
            return $totalCount;
        }
        return $usages;
    }

    /**
     * Replaces the a term by another in all its usages.
     * @param ObjectRepository $em
     * @param $vocTermFrom
     * @param $voctTermTo
     */
    public function replaceTermInUsages(EntityManager $em, $vocTermFrom, $vocTermTo)
    {
        $usages = $this->getTermUsages($em, $vocTermFrom, $count = false);
        $propAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($usages as $class =>$classUsage) {
            foreach ($classUsage['entities'] as $entity) {
                $ent = $em->getRepository($class)->findBy(array('id'=>$entity->getId()));
                $ent = $ent[0];
                //echo $entity->getName()."->".$classUsage['fieldName'];

                $value = null;

                if ($classUsage['multiple'] == true) {
                    $value = $propAccessor->getValue($ent, $classUsage['fieldName']);
                } else {
                    $value = $propAccessor->getValue($ent, $classUsage['fieldName']);
                }

                $vocClass = get_class($vocTermTo);
                if ($value instanceof $vocClass) {

                    $propAccessor->setValue($entity, $classUsage['fieldName'], $vocTermTo);
                } else {

                    $termInCollection = $this->checkTermIsInCollection($vocTermTo, $value);
                    if (is_array($value)) {
                        for ($pos = 0; $pos < count($value); $pos++) {
                            if (method_exists($value[$pos], 'getId') && ($value[$pos]->getId() == $vocTermFrom->getId())) {
                                //if destination element is not already present in collection, we can do a replacement
                                if ($termInCollection) {
                                    $value = array_splice($value, $pos);
                                    break;
                                } else {
                                    $value[$pos] = $vocTermTo;
                                }
                            }
                        }
                        $propAccessor->setValue($entity, $classUsage['fieldName'], $value);
                    } else if ($value instanceof \Traversable) {
                        foreach ($value as $key => $val) {
                            if (method_exists($val, 'getId') && ($val->getId() == $vocTermFrom->getId())) {
                                if ($termInCollection) {
                                    $value->remove($key);
                                    break;
                                } else {
                                    $value->offsetSet($key, $vocTermTo);
                                }
                            }
                        }
                        $propAccessor->setValue($entity, $classUsage['fieldName'], $value);
                    }
                }
            }
        }

        $em->flush();
    }

    /**
     * @param $term
     * @param $collection
     * @return bool
     */
    protected function checkTermIsInCollection($term, $collection)
    {
        $isInCollection = false;

        if ($collection instanceof \Traversable) {
            $collection = $collection->toArray();
        }

        foreach ($collection as $key => $val) {

            if (method_exists($val, 'getId') && ($val->getId() == $term->getId())) {
                $isInCollection = true;
            }
        }


        return $isInCollection;
    }
}
