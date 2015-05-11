<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 05/06/14
 * Time: 15:13
 */

namespace Sygefor\Bundle\ListBundle\HumanReadablePropertyAccessor;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * instantiates
 * Class HumanReadablePropertyAccessorFactory
 * @package Sygefor\Bundle\ListBundle\HumanReadablePropertyAccessor
 */
class HumanReadablePropertyAccessorFactory
{

    /**
     * @var
     */
    protected $termCatalog;

    /** @var  EntityManager */
    protected $em;

    /**
     *
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * @param $termCatalog
     */
    public function setTermCatalog($termCatalog)
    {
        //factory is given an alternate version of configuration array, indexed by each entry corresponding className
        foreach ( $termCatalog as $confEntry ) {
            $class = $this->getClassName($confEntry['class']);
            if (!empty($confEntry['parent']) && !empty($termCatalog[$confEntry['parent']]) ) {
                $this->termCatalog[$class] = $termCatalog[$confEntry['parent']] ;
            } else {
                $this->termCatalog[$class] = $confEntry ;
            }

        }
    }

    /**
     * @param $class
     * @throws \Exception
     * @return
     */
    public function getTermCatalog($class = null)
    {
        if($class) {
            if(!isset($this->termCatalog[$this->getClassName($class)])) {

                throw new \Exception("no catalog for this class : " . $class);
            }
            return $this->termCatalog[$this->getClassName($class)];
        } else {
            return $this->termCatalog;
        }
    }

    /**
     * @param $class
     */
    public function getEntityAlias($class = null)
    {
        if(!isset($this->termCatalog[$this->getClassName($class)])) {
            throw new \Exception("no catalog for this class : " . $class);
        } else if (!isset($this->termCatalog[$this->getClassName($class)]['alias'])) {
            return null;
        } else {
            return $this->termCatalog[$this->getClassName($class)]['alias'];
        }
    }

    /**
     * Returns
     * @return array
     */
    public function getKnownEntities()
    {
        $entityTypes = array();
        foreach($this->termCatalog as $entity) {
            $entityTypes[$entity['class']] = $entity['alias'] ;
        }
        return $entityTypes;
    }

    /**
     * Returns true if given class has an entry in term catalog.
     * @param string $className
     * @return boolean
     */
    public function hasEntry($className)
    {
        $class = $this->getClassName($className);
        return isset($this->termCatalog[$class]);
    }

    /**
     * creates an accessor for the given object
     * @param $object
     * @return OpenTBSPropertyAccessor
     */
    public function getAccessor($object)
    {
        $propertyAccessor = new HumanReadablePropertyAccessor($object);
        $propertyAccessor->setAccessorFactory($this);
        return $propertyAccessor;
    }

    /**
     * Returns mail path for entity if defined, null otherwise
     * @param $class
     * @return string|null
     */
    public function getMailPath($class)
    {
        $class = $this->getClassName($class);
        if (isset ($this->termCatalog[$class]) && isset ($this->termCatalog[$class]['emailPath'])) {
            return $this->termCatalog[$class]['emailPath'];
        }
        return null;
    }

    /**
     * returns the corresponding property for given class/alias, null if not found in catalog
     * @param $class
     * @param $alias
     * @return string|null
     */
    public function getPropertyForAlias($class, $alias)
    {
        $class = $this->getClassName($class);
        if (isset ($this->termCatalog[$class]) && isset ($this->termCatalog[$class]['fields'][$alias])) {
            return $this->termCatalog[$class]['fields'][$alias]['property'];
        }
        return null;
    }

    /**
     * returns the corresponding format for given class/alias (typically date formats), null if not found in catalog
     * @param $class
     * @param $alias
     * @return string|null
     */
    public function getFormatForAlias($class, $alias)
    {
        $class = $this->getClassName($class);
        if (isset ($this->termCatalog[$class]) &&
            isset ($this->termCatalog[$class]['fields'][$alias]) &&
            isset ($this->termCatalog[$class]['fields'][$alias]['format'])) {
            return $this->termCatalog[$class]['fields'][$alias]['format'];
        }
        return null;
    }

    /**
     * Provides the class real name (useful for proxy classes)
     * @param $className
     * @return string
     */
    protected function getClassName($className)
    {
        try{
            $absClassName = $this->em->getClassMetadata($className)->getName();
        } catch (MappingException $e) {
            $absClassName = $className;
        }

        return $absClassName;
    }

}
