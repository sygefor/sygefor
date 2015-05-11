<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 17/04/14
 * Time: 15:54
 */

namespace Sygefor\Bundle\TrainingBundle\Tests\Controller\Security\Authorization\AccessRight;


use Sygefor\Bundle\TrainingBundle\Security\Authorization\AccessRight\OwnTrainingAccessRight;

class OwnTrainingAccessRightTest  extends \PHPUnit_Framework_TestCase
{
    public function testAccessRightShouldReturnCorrectLabel()
    {
        $AccessRight = new OwnTrainingAccessRight();
        $this->assertEquals("Gestion des formations de sa propre URFIST", $AccessRight->getLabel());
    }

    public function testAccessRightShouldSupportUserClass()
    {
        $AccessRight = new OwnTrainingAccessRight();
        $this->assertEquals(true,$AccessRight->supportsClass('Sygefor\Bundle\TrainingBundle\Entity\Training'));
        $this->assertEquals(false,$AccessRight->supportsClass('Sygefor\Bundle\TrainingBundle\Entity\TrainingTest'));
        $this->assertEquals(true,$AccessRight->supportsClass('Sygefor\Bundle\TrainingBundle\Entity\DiverseTraining'));
        $this->assertEquals(true,$AccessRight->supportsClass('Sygefor\Bundle\TrainingBundle\Entity\Meeting'));
        $this->assertEquals(true,$AccessRight->supportsClass('Sygefor\Bundle\TrainingBundle\Entity\TrainingCourse'));
        $this->assertEquals(true,$AccessRight->supportsClass('Sygefor\Bundle\TrainingBundle\Entity\Internship'));
    }

    public function testAccessRightIsGrantedToUser()
    {
        $AccessRight = new OwnTrainingAccessRight();
        $org1 = 'fooOrg';
        $org2 = 'barOrg';

        $user1 = $this->getMock('Sygefor\Bundle\TrainingBundle\Entity\Training');
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
        $object = $this->getMock('Sygefor\Bundle\TrainingBundle\Entity\Training');
        $object->expects($this->any())
        ->method('getOrganization')
        ->will($this->returnValue($org1));

        $this->assertEquals(true, $AccessRight->isGranted($token1, $object, null));
        $this->assertEquals(false, $AccessRight->isGranted($token2, $object, null));
        $this->assertEquals(true, $AccessRight->isGranted($token1, null, null));
    }
} 