<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 07/04/14
 * Time: 11:15.
 */
namespace Sygefor\Bundle\CoreBundle\BatchOperation;

/**
 * Class BatchOperationRegistry.
 */
class BatchOperationRegistry
{
    /**
     * @var array
     */
    private $operations = array();

    /**
     * @param BatchOperationInterface $batchOperation
     * @param $id
     */
    public function addBatchOperation(BatchOperationInterface $batchOperation, $id)
    {
        $batchOperation->setId($id);

        //storing batch operation
        $this->operations[$id] = $batchOperation;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->operations;
    }

    /**
     * @param $id
     * @return array|void
     */
    public function get($id)
    {
        if (isset($this->operations[$id])) {
            return $this->operations[$id];
        }

        return;
    }
}
