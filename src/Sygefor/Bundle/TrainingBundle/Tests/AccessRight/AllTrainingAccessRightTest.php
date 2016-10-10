<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 17/04/14
 * Time: 15:54.
 */
namespace Sygefor\Bundle\TrainingBundle\Tests\Controller\Security\Authorization\AccessRight;

use Sygefor\Bundle\TrainingBundle\Security\Authorization\AccessRight\AllTrainingAccessRight;

class AllTrainingAccessRightTest  extends \PHPUnit_Framework_TestCase
{
    public function testAccessRightShouldReturnCorrectLabel()
    {
        $AccessRight = new AllTrainingAccessRight();
        $this->assertSame('Gestion des formations de tous les centres', $AccessRight->getLabel());
    }

    public function testAccessRightShouldSupportUserClass()
    {
        $AccessRight = new AllTrainingAccessRight();
        $this->assertSame(true, $AccessRight->supportsClass('Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining'));
    }

    public function testAccessRightIsGrantedToUser()
    {
        $AccessRight = new AllTrainingAccessRight();
        $org1        = 'fooOrg';
        $org2        = 'barOrg';

        $user1 = $this->getMock('Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining');
        $user1->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org1));

        $token1 = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token1->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user1));

        $user2 = $this->getMock('Sygefor\Bundle\CoreBundle\Entity\User\User');
        $user2->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org2));

        $token2 = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token2->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user2));

        //mocking User object although type is of no importance for current test
        $object = $this->getMock('Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining');
        $object->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org1));

        $this->assertSame(true, $AccessRight->isGranted($token1, $object, null));
        $this->assertSame(true, $AccessRight->isGranted($token2, $object, null));
        $this->assertSame(true, $AccessRight->isGranted($token1, null, null));
    }
}
