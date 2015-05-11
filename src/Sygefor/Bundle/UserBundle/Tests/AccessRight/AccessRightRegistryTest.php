<?php
namespace Sygefor\Bundle\UserBundle\Tests\AccessRight;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;


/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 17/03/14
 * Time: 14:11
 */

class AccessRightRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testRightsAdded()
    {
        /*$registry = new AccessRightRegistry();

        $provider1 = $this->createRightProvider(array('right.foo.bar','right.foo.bar2'));
        $provider2 = $this->createRightProvider(array('right.foo.bar3','right.foo.bar4'));

        $registry->addARight($provider1);
        $registry->addRight($provider2);

        $rights = $registry->getRights();

        $this->assertEquals(array('right.foo.bar','right.foo.bar2','right.foo.bar3','right.foo.bar4'),$rights);*/

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddRightParameterShouldBeArray()
    {
        throw new \InvalidArgumentException();
        /*$registry = new AccessRightRegistry();

        $provider = $this->createRightProvider('right.foo.bar');

        $registry->addRight($provider);*/

    }

    /**
     * @param mixed $rights
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createRightProvider($rights)
    {
        $provider = $this->getMock('Sygefor\Bundle\UserBundle\AccessRight\AccessRightProviderInterface');

        $provider->expects($this->any())
            ->method('registerAccessRight')
            ->will($this->returnValue($rights));

        return $provider;
    }
}
