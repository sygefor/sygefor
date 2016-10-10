<?php

namespace Sygefor\Bundle\CoreBundle\BatchOperation;

use Doctrine\ORM\EntityManager;

/**
 * Class AbstractBatchOperation.
 */
abstract class AbstractBatchOperation implements BatchOperationInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    protected $targetClass;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $class
     */
    public function setTargetClass($class)
    {
        $this->targetClass = $class;
    }

    /**
     * @return string
     */
    public function getTargetClass()
    {
        return $this->targetClass;
    }

    /**
     * @var string
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * the label for operation (will be displayed in available operations list).
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param EntityManager $em
     */
    public function setEm(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Return entity array with id list.
     *
     * @param $idList
     *
     * @return array
     */
    protected function getObjectList($idList)
    {
        return $this->em->getRepository($this->targetClass)->findBy(array('id' => $idList));
    }

    /**
     * Re-order a list by keys.
     */
    protected function reorderByKeys(&$items, $keys)
    {
        uksort($items, function ($a, $b) use ($keys) {
            $position_a = array_search($a, $keys, true);
            $position_b = array_search($b, $keys, true);

            return $position_a < $position_b ? -1 : 1;
        });
    }
}
