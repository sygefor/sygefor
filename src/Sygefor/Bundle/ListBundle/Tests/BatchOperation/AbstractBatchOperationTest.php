<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 10/04/14
 * Time: 12:09
 */

namespace Sygefor\Bundle\ListBundle\Tests\BatchOperation;


class AbstractBatchOperationTest extends \PHPUnit_Framework_TestCase
{

    public function testAbstractBatchOperation()
    {
        $batchOp = $this->getMockForAbstractClass('Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation');

        $batchOp->setId('foo');
        $this->assertEquals('foo', $batchOp->getId());

        $batchOp->setLabel('label foo');
        $this->assertEquals('label foo', $batchOp->getLabel());

        $batchOp->setTargetClass('MyBundle:FooClass');
        $this->assertEquals('MyBundle:FooClass', $batchOp->getTargetClass());

        $batchOp->setOptions(array('opt1'=>'foo', 'opt2'=>'bar'));
        $this->assertAttributeEquals(array('opt1'=>'foo', 'opt2'=>'bar'), 'options', $batchOp);
    }
} 