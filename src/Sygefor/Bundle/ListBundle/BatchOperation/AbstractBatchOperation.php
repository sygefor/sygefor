<?php
namespace Sygefor\Bundle\ListBundle\BatchOperation;


/**
 * Class AbstractBatchOperation
 * @package Sygefor\Bundle\ListBundle\BatchOperation
 */
abstract class AbstractBatchOperation implements BatchOperationInterface
{
    /**
     * @var array $options
     */
    protected $options = array();

    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $label
     */
    private $label;

    /**
     * @var string $targetClass
     */
    protected $targetClass;

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);

    }

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
        return $this->targetClass ;
    }

    /**
     * @var string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return the label for opertion (will be displayed in available operations list)
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Re-order a list by keys
     */
    protected function reorderByKeys(&$items, $keys) {
        uksort($items, function($a, $b) use ($keys) {
            $position_a = array_search( $a, $keys ) ;
            $position_b = array_search( $b, $keys ) ;
            return  $position_a < $position_b ? -1 : 1 ;
        });
    }
}
