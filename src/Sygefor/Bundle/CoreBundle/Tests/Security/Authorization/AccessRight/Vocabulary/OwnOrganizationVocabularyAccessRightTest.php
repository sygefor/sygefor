<?php

namespace Sygefor\Bundle\CoreBundle\Tests\Security\Authorization\AccessRight\Vocabulary;

use Sygefor\Bundle\CoreBundle\Security\Authorization\AccessRight\OwnOrganizationVocabularyAccessRight;

class OwnOrganizationVocabularyAccessRightTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessRightShouldReturnCorrectLabel()
    {
        $AccessRight = new OwnOrganizationVocabularyAccessRight();

        $this->assertSame('Gestion des vocabulaires de son propre CRFCB', $AccessRight->getLabel());
    }

    public function testAccessRightShouldSupportUserClass()
    {
        $AccessRight = new OwnOrganizationVocabularyAccessRight();

        $this->assertSame(false, $AccessRight->supportsClass('Foo\Bar\Class'));
        $this->assertSame(false, $AccessRight->supportsClass('Sygefor\Bundle\CoreBundle\Tests\Entity\MyNationalVocabulary'));
        $this->assertSame(true, $AccessRight->supportsClass('Sygefor\Bundle\CoreBundle\Tests\Entity\MyOrganizationVocabulary'));
        $this->assertSame(true, $AccessRight->supportsClass('Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface'));
    }

    public function testAccessRightIsGrantedToUser()
    {
        $AccessRight = new OwnOrganizationVocabularyAccessRight();
        $org1        = 'fooOrg';
        $org2        = 'barOrg';

        $user1 = $this->getMock('Sygefor\Bundle\CoreBundle\Entity\User\User');
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
        $object = $this->getMock('Sygefor\Bundle\CoreBundle\Tests\Entity\MyOrganizationVocabulary');
        $object->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org1));

        $this->assertSame(true, $AccessRight->isGranted($token1, $object, null));
        $this->assertSame(false, $AccessRight->isGranted($token2, $object, null));
        $this->assertSame(false, $AccessRight->isGranted($token1, null, null));
        $this->assertSame(false, $AccessRight->isGranted($token1, 'Sygefor\Bundle\CoreBundle\Entity\VocabularyInterface', null));
    }
}
