<?php
namespace Sygefor\Bundle\TrainingBundle\Registry;

class TrainingTypeRegistry
{
    /**
     * @var array
     */
    private $types;

    /**
     * @param $types
     */
    function __construct($types) {
        $this->types = $types;
    }

    /**
     * @param array $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param $type
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getType($type)
    {
        if(!isset($this->types[$type])) {
            throw new \InvalidArgumentException("Invalid training type : " . $type);
        }
        return $this->types[$type];
    }

}
