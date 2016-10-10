<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 01/09/14
 * Time: 14:43.
 */
namespace Sygefor\Bundle\CoreBundle\MappingProvider;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class CascadeUpdater.
 *
 * @todo : HANDLE COMPOSITE OR CUSTOM ID CONFIGURATION
 */
class MappingProvider
{
    /** @var  array mapping elastica mapping */
    protected $mapping;

    /**
     * @var
     */
    private $entities = array();

    /**
     * @var
     */
    private $visitedEntities = array();

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * @param $mapping
     * @param \Symfony\Component\DependencyInjection\Container $container
     *
     * @internal param $em
     */
    public function __construct($mapping, Container $container)
    {
        $this->mapping   = $mapping;
        $this->container = $container;
    }

    /**
     * returns an array of mapping for given class if known.
     *
     * @param $class
     */
    public function getMappingByClass($class)
    {
        $class = $this->aliasToClass($class);
        foreach ($this->mapping as $index => $indexMapping) {
            foreach ($indexMapping['types'] as $mapElt) {
                if ( ! empty($mapElt['config']['persistence']) && ($class === $mapElt['config']['persistence']['model'])) {
                    return $mapElt['mapping']['properties'];
                }
            }
        }

        return;
    }

    /**
     * @param $class
     *
     * @return int|null|string
     */
    public function getMappingNameByClass($class)
    {
        $class = $this->aliasToClass($class);
        foreach ($this->mapping as $index => $indexMapping) {
            foreach ($indexMapping['types'] as $key => $mapElt) {
                if ( ! empty($mapElt['config']['persistence']) && ($class === $mapElt['config']['persistence']['model'])) {
                    return array($index, $key);
                }
            }
        }

        return;
    }

    /**
     * @param $entityClass
     * @param $path
     *
     * @return bool
     */
    public function classMappingContainsPath($entityClass, $path)
    {
        $class = $this->aliasToClass($entityClass);

        //if no dots are present in path, checking properties
        if(strpos($path, '.') <= 0){
            $tmp = $this->getMappingByClass($class);

            return ! empty($tmp[$path]);
        }
        else {
            $steps = explode('.', $path);
            $tmp   = $this->getMappingByClass($class);
            array_shift($steps);

            return $this->classMappingArrayContainsPath($tmp, $steps);
        }
    }

    /**
     * triggering index updates.
     */
    public function updateIndex()
    {
        foreach ($this->entities as $class => $entities) {
            list($index, $type) = $this->getMappingNameByClass($class);
            if($index && $type) {
                $serviceId = 'fos_elastica.object_persister.' . $index . '.' . $type;
                if ($this->container->has($serviceId) && ! empty($entities)) {
                    $this->container->get($serviceId)->replaceMany($entities);
                }
            }
        }
        $this->container->get('fos_elastica.index')->refresh();
    }

    /**
     * @return string
     */
    public function getStats()
    {
        $str = '';
        foreach ($this->entities as $key => $class) {
            $str .= $key . ': [' . count($class) . '] ';
            foreach ($class as $ent) {
                $str .= $ent->getId() . ' ';
            }
            $str .= "\n";
        }

        return $str;
    }

    /**
     * @param $mappingArray
     * @param $pathArray
     *
     * @return bool
     */
    private function classMappingArrayContainsPath($mappingArray, $pathArray)
    {
        if (empty($mappingArray[$pathArray[0]])) {
            return false;
        }
        //from now on, we can say an entry exists in mapping for current path.
        if ( count($pathArray) === 1 ){
            return true;
        } else {
            array_pop($pathArray);

            return $this->classMappingArrayContainsPath($mappingArray[$pathArray[0]]['properties'], $pathArray);
        }
    }

    /**
     * @param $class
     *
     * @return string
     */
    private function aliasToClass($class)
    {
        if (strpos($class, ':') > 0) {
            return $this->container->get('doctrine.orm.entity_manager')->getClassMetadata($class)->getName();
        }

        return $class;
    }

    /**
     * @param $entityClass
     * @param $entityProperty
     * @param $entityId
     * @param bool  $onlyManyToMany
     * @param array $path
     */
    public function findLinkedEntities($entityClass, $entityProperty, $entityId, $onlyManyToMany = false, $path = array())
    {
        /** @var ClassMetaData $metadata */
        $metadata                = $this->container->get('doctrine.orm.entity_manager')->getClassMetadata($entityClass);
        $this->visitedEntities[] = $metadata->getReflectionClass()->getName();

        //@todo hm: filter using input properties :
        // a targetentity whose mapping doesnt include any of the entityproperties has no interest to be inspected
        foreach ($metadata->associationMappings as $fieldName => $fieldMD) {
            //if already visited, we stop
            if ( ! empty($fieldMD['targetEntity']) && ! in_array($fieldMD['targetEntity'], $this->visitedEntities, true)) {
                //for relations of the type "many to many" OR any relation
                $tmpArr = $path;

                //getting next entity field that targets current entity
                $invPath = ( ! empty($fieldMD['inversedBy'])) ? $fieldMD['inversedBy'] : $fieldMD['mappedBy'];

                if ( ! empty($invPath)) {
                    array_push($tmpArr, $invPath);

                    // we can continue exploration if next entity refers to current entity/field AND
                    if ($this->classMappingContainsPath($fieldMD['targetEntity'], implode('.', $tmpArr))) {
                        $vIds = $this->getConcernedEntities($fieldMD, $entityId);
                        $this->findLinkedEntities($fieldMD['targetEntity'], array($invPath), $vIds, false, $tmpArr);
                    }
                }
            }
        }
    }

    /**
     * @param $entity
     *
     * @return array
     *
     * @todo : HANDLE COMPOSITE OR CUSTOM ID CONFIGURATION
     */
    public function getPostDeletionCommandLines($entity)
    {
        $metaData = $this->container->get('doctrine.orm.entity_manager')->getClassMetadata(get_class($entity));
        $commands = array();
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach($metaData->associationMappings as $fieldName => $fieldMD) {
            $invPath = ( ! empty($fieldMD['inversedBy']) ) ? $fieldMD['inversedBy']  : $fieldMD['mappedBy'];
            if ( ! empty($invPath)) {
                $value = $accessor->getValue($entity, $fieldName);
                if($value) {
                    if($value instanceof \Traversable) {
                        foreach($value as $tEntity) {
                            if(method_exists($tEntity, 'getId')) {
                                $commands[] = array($tEntity->getId(), get_class($tEntity), $invPath);
                            }
                        }
                    } else {
                        if(method_exists($value, 'getId')) {
                            $commands[] = array($value->getId(), get_class($value), $invPath);
                        }
                    }
                }
            }
        }

        return $commands;
    }

    /**
     * @param $fieldMD
     * @param $id
     *
     * @return array
     *
     * @todo : HANDLE COMPOSITE OR CUSTOM ID CONFIGURATION
     */
    private function getConcernedEntities($fieldMD, $id)
    {
        $type    = $fieldMD['targetEntity'];
        $invPath = ( ! empty($fieldMD['inversedBy'])) ? $fieldMD['inversedBy']  : $fieldMD['mappedBy'];
        /** @var Query $query */
        $query = null;
        if ( ! empty($invPath)) {
            $property = $invPath;
            $query    = $this->container->get('doctrine.orm.entity_manager')->getRepository($type)->createQueryBuilder('e')
                //->select('e.id')
                ->leftJoin('e.' . $property, 'ej')
                ->where('ej.id IN (:ids)')->setParameter('ids',  $id)
                ->getQuery();
        }
        else {
            $query = $this->container->get('doctrine.orm.entity_manager')->getRepository($type)->createQueryBuilder('e')
                //->select('e.id')
                ->where('e.id IN (:ids)')->setParameter('ids', $id)
                ->getQuery();
        }

        if (empty($this->entities[$type])) {
            $this->entities[$type] = array();
        }

        $res = $query->getResult();

        $returnedIds = array();
        foreach ($res as $r) {
            if (method_exists($r, 'getId')) {
                $returnedIds [] = $r->getId();
            }
        }

        $this->entities[$type] = array_merge($this->entities[$type], $res);

        return $returnedIds;
    }
}
