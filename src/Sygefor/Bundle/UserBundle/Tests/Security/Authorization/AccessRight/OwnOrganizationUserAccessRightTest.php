<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 21/03/14
 * Time: 14:33
 */
namespace Sygefor\Bundle\UserBundle\Tests\Security\Authorization\AccessRight;

use Sygefor\Bundle\UserBundle\Security\Authorization\AccessRight\OwnOrganizationUserAccessRight;

class OwnOrganizationUserAccessRightTest extends \PHPUnit_Framework_TestCase
{

    public function testAccessRightShouldReturnCorrectLabel()
    {
        $AccessRight = new OwnOrganizationUserAccessRight();

        $this->assertEquals("Gestion des utilisateurs de sa propre URFIST", $AccessRight->getLabel());
    }

    public function testAccessRightShouldSupportUserClass()
    {
        $AccessRight = new OwnOrganizationUserAccessRight();

        $this->assertEquals(false,$AccessRight->supportsClass('Foo\Bar\Class'));
        $this->assertEquals(true,$AccessRight->supportsClass('Sygefor\Bundle\UserBundle\Entity\User'));
    }

    public function testAccessRightIsGrantedToUser()
    {
        $AccessRight = new OwnOrganizationUserAccessRight();

        $org1 = 'fooOrg';
        $org2 = 'barOrg';

        $user1 = $this->getMock('Sygefor\Bundle\UserBundle\Entity\User');
        $user1->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org1));

        $token1 = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token1->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user1));

        $user2 = $this->getMock('Sygefor\Bundle\UserBundle\Entity\User');
        $user2->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org2));

        $token2=$this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token2->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user2));

        //mocking User object although type is of no importance for current test
        $object = $this->getMock('Sygefor\Bundle\UserBundle\Entity\User');
        $object->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org1));

        $this->assertEquals(true, $AccessRight->isGranted($token1, $object, null));
        $this->assertEquals(false, $AccessRight->isGranted($token2, $object, null));
        $this->assertEquals(true, $AccessRight->isGranted($token1, null, null));

    }

} 