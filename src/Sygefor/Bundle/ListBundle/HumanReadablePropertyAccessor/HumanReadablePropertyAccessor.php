<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 05/06/14
 * Time: 14:54
 */

namespace Sygefor\Bundle\ListBundle\humanReadablePropertyAccessor;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Accesses an object property using human readable objects and property names given in config
 * Class OpenTBSPropertyAccessor
 * @package Sygefor\Bundle\ListBundle\OpenTBSPropertyAccessor
 */
class HumanReadablePropertyAccessor
{
    /** @var  HumanReadablePropertyAccessorFactory $accessorFactory */
    private $accessorFactory;

    /**
     * @var Object currently accessed objectg
     */
    private $object;

    /**
     * @param $object
     */
    public function __construct($object)
    {
        $this->object = $object ;
    }

    /**
     * Return an array of accessors from the object properties
     * @return array
     */
    public function toArray() {
        $catalog = $this->accessorFactory->getTermCatalog(get_class($this->object));
        $return = array();

        foreach($catalog['fields'] as $name => $options) {

            if ( ( is_object( $this->$name)) && $this->accessorFactory->hasEntry( get_class($this->$name))) {
                $return[$name] = $this->accessorFactory->getAccessor($this->$name)->toArray();
            } else if (is_object ($this->$name) && get_class($this->$name) == get_class($this)) {
                $return[$name] = $this->$name->toArray();
            }else if(empty($this->$name)) {
                $return[$name] = array();
            } else {
                $return[$name] = $this->$name;
            }

        }
        return $return;
    }

    /**
     * magic getter for property path.
     * @param $property a string on the form 'myObjectAlias.MypropertyAlias'
     * @return mixed|null
     */
    public function __get($property)
    {
        switch($property) {
            case 'email':
                //specific behaviour for retrieving mail attached to an entity (such as trainee, inscription, trainer, ...)

                $mailPath = $this->accessorFactory->getMailPath( get_class($this->object) );
                if ($mailPath != null) {
                    $accessor = PropertyAccess::createPropertyAccessor();
                    return $accessor->getValue($this->object, $mailPath);

                }
                return null;
                break;
            default:
                //default behaviour
                //path
                $expl = explode('.', $property);
                //path may or may not contains dots. In the former case we need to split it in prefix and suffix parts.
                if (count($expl) == 1) {
                    $prefix = $property;
                    $suffix = '';
                } else {
                    $prefix = $expl[0];
                    $suffix = implode('.', array_slice($expl, 1));
                }

                $path = $this->accessProperty($prefix);

                //trying to get property for path suffix
                try {
                    $accessor = PropertyAccess::createPropertyAccessor();
                    $value = $accessor->getValue($this->object, $path);

                } catch (NoSuchPropertyException $e) {
                    // asked property was not found in object
                    // (alias did not correspond to something that actually exits
                    // thus an explicit mention is returned and is displayed in result file
                    return 'Non défini';
                }
                catch (UnexpectedTypeException $e) {
                    // asked property was not found in object
                    // (alias did not correspond to something that actually exits
                    // thus an explicit mention is returned and is displayed in result file
                    return 'Non défini';
                }

                // if suffix is not empty, we continue along path
                if ($suffix != '') {
                    if (is_object($value) && $this->accessorFactory->hasEntry(get_class($value))) {
                        //new property accessor for object.

                        /** @var OpenTBSPropertyAccessor $nextAccessor */
                        $nextAccessor = $this->accessorFactory->getAccessor($value);
                        if ($nextAccessor) {
                            return $nextAccessor->$suffix;
                        }
                    }
                } else { //we reached end of path

                    if (is_object($value) && $this->accessorFactory->hasEntry(get_class($value))) {
                        return $this->accessorFactory->getAccessor($value);
                    } else if ($value instanceof \Traversable) {
                        $arr = new ArrayCollection();
                        foreach ($value as $val) {
                            if($this->accessorFactory->hasEntry(get_class($val))){
                                $arr->add($this->accessorFactory->getAccessor($val));
                            }
                        }
                        return $arr;
                    }
                }

                //an attempt of formatting is done
                return $this->format($prefix, $value);
                break;
        }
    }

    /**
     * @param $name
     * @return boolean
     */
    public function __isset($name)
    {
        //@todo hm: refine this
        return true;
    }

    private function accessProperty($property) {
        return $this->accessorFactory->getPropertyForAlias( get_class($this->object), $property);
    }

    /**
     * @param mixed $accessorFactory
     */
    public function setAccessorFactory($accessorFactory)
    {
        $this->accessorFactory = $accessorFactory;
    }

    /**
     * @return mixed
     */
    public function getAccessorFactory()
    {
        return $this->accessorFactory;
    }

    /**
     * @param mixed $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * returns a formatted version of requested value. useful for date for the moment
     * @param $value
     * @return mixed
     */
    private function format($prefix, $value)
    {
        $format = $this->accessorFactory->getFormatForAlias(get_class($this->object), $prefix);
        if ($value instanceof \DateTime) {
            if ($format) {
                /** @var \DateTime $value */
                return $value->format($format);
            } else {
                return $value->format('d/m/Y');
            }
        }else if (is_bool($value)) {
            if ($value) return "oui";
            return "non";
        }
        return $value;
    }

}
