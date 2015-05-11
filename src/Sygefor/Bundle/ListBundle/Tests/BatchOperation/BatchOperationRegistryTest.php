<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 10/04/14
 * Time: 11:28
 */

namespace Sygefor\Bundle\ListBundle\Tests\BatchOperation;

use Sygefor\Bundle\ListBundle\BatchOperation\BatchOperationRegistry;

class BatchOperationRegistryTest extends \PHPUnit_Framework_TestCase
{

    public function testRegistryShouldRegisterBatchOperation(){
        $registry = new BatchOperationRegistry();

        $batchOp1 = $this->createBatchOperation('sygefor_list.batch.foo','operation foo');
        $batchOp2 = $this->createBatchOperation('sygefor_list.batch.bar','operation bar');


        $registry->addBatchOperation($batchOp1, 'sygefor_list.batch.foo', 'FooBundle:Class1', 'operation foo');
        $registry->addBatchOperation($batchOp2, 'sygefor_list.batch.bar', 'FooBundle:Class2', 'operation bar');

        $this->assertEquals(2,count( $registry->getAll()));

        $obj = $registry->get('sygefor_list.batch.foo');
        $this->assertInstanceOf('Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation', $obj);

        $obj = $registry->get('sygefor_list.batch.baz');
        $this->assertEquals( null , $obj );

    }


    /**
     * @param String $tag
     * @param array $attrs
     * @return Definition
     */
    private function createBatchOperation($id, $label)
    {
        //$provider = $this->getMockForAbstractClass('Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation');
        $batchOperation = $this->getMock('Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation');
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