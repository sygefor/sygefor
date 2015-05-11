<?php

namespace Sygefor\Bundle\TaxonomyBundle\Tests\Security\Authorization\AccessRight;

use Sygefor\Bundle\TaxonomyBundle\Security\Authorization\AccessRight\OwnOrganizationVocabularyAccessRight;

class OwnOrganizationVocabularyAccessRightTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessRightShouldReturnCorrectLabel()
    {
        $AccessRight = new OwnOrganizationVocabularyAccessRight();

        $this->assertEquals("Gestion des vocabulaires de sa propre URFIST",$AccessRight->getLabel());
    }

    public function testAccessRightShouldSupportUserClass()
    {
        $AccessRight = new OwnOrganizationVocabularyAccessRight();

        $this->assertEquals(false,$AccessRight->supportsClass('Foo\Bar\Class'));
        $this->assertEquals(false,$AccessRight->supportsClass('Sygefor\Bundle\TaxonomyBundle\Tests\Entity\MyNationalVocabulary'));
        $this->assertEquals(true,$AccessRight->supportsClass('Sygefor\Bundle\TaxonomyBundle\Tests\Entity\MyOrganizationVocabulary'));
        $this->assertEquals(true,$AccessRight->supportsClass('Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface'));
    }

    public function testAccessRightIsGrantedToUser()
    {
        $AccessRight = new OwnOrganizationVocabularyAccessRight();
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
        $object = $this->getMock('Sygefor\Bundle\TaxonomyBundle\Tests\Entity\MyOrganizationVocabulary');
        $object->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org1));

        $this->assertEquals(true, $AccessRight->isGranted($token1, $object, null));
        $this->assertEquals(false, $AccessRight->isGranted($token2, $object, null));
        $this->assertEquals(false, $AccessRight->isGranted($token1, null, null));
        $this->assertEquals(false, $AccessRight->isGranted($token1, 'Sygefor\Bundle\TaxonomyBundle\Entity\LocalVocabularyInterface', null));
    }

}
