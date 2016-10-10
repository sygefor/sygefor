<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 10/04/14
 * Time: 11:28.
 */
namespace Sygefor\Bundle\CoreBundle\Tests\BatchOperation;

use Sygefor\Bundle\CoreBundle\BatchOperation\BatchOperationRegistry;

class BatchOperationRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testRegistryShouldRegisterBatchOperation() {
        $registry = new BatchOperationRegistry();

        $batchOp1 = $this->createBatchOperation('sygefor_core.batch.foo', 'operation foo');
        $batchOp2 = $this->createBatchOperation('sygefor_core.batch.bar', 'operation bar');

        $registry->addBatchOperation($batchOp1, 'sygefor_core.batch.foo', 'FooBundle:Class1', 'operation foo');
        $registry->addBatchOperation($batchOp2, 'sygefor_core.batch.bar', 'FooBundle:Class2', 'operation bar');

        $this->assertSame(2, count( $registry->getAll()));

        $obj = $registry->get('sygefor_core.batch.foo');
        $this->assertInstanceOf('Sygefor\Bundle\CoreBundle\BatchOperation\AbstractBatchOperation', $obj);

        $obj = $registry->get('sygefor_core.batch.baz');
        $this->assertSame( null, $obj );
    }

    /**
     * @param $id
     * @param $label
     * @return mixed
     */
    private function createBatchOperation($id, $label)
    {
        //$provider = $this->getMockForAbstractClass('Sygefor\Bundle\CoreBundle\BatchOperation\AbstractBatchOperation');
        $batchOperation = $this->getMock('Sygefor\Bundle\CoreBundle\BatchOperation\AbstractBatchOperation');
        $batchOperation->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        $batchOperation->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue($label));

        $batchOperation->expects($this->any())
            ->method('setLabel');
        $batchOperation->expects($this->any())
            ->method('setId');

        return $batchOperation;
    }
}
