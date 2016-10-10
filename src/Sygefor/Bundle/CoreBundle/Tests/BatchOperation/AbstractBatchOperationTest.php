<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 10/04/14
 * Time: 12:09.
 */
namespace Sygefor\Bundle\CoreBundle\Tests\BatchOperation;

class AbstractBatchOperationTest extends \PHPUnit_Framework_TestCase
{
    public function testAbstractBatchOperation()
    {
        $batchOp = $this->getMockForAbstractClass('Sygefor\Bundle\CoreBundle\BatchOperation\AbstractBatchOperation');

        $batchOp->setId('foo');
        $this->assertSame('foo', $batchOp->getId());

        $batchOp->setLabel('label foo');
        $this->assertSame('label foo', $batchOp->getLabel());

        $batchOp->setTargetClass('MyBundle:FooClass');
        $this->assertSame('MyBundle:FooClass', $batchOp->getTargetClass());

        $batchOp->setOptions(array('opt1' => 'foo', 'opt2' => 'bar'));
        $this->assertAttributeSame(array('opt1' => 'foo', 'opt2' => 'bar'), 'options', $batchOp);
    }
}
