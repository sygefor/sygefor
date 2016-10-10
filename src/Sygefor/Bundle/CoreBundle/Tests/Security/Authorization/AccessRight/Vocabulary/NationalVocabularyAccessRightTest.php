<?php

namespace Sygefor\Bundle\CoreBundle\Tests\Security\Authorization\AccessRight\Vocabulary;

use Sygefor\Bundle\CoreBundle\Security\Authorization\AccessRight\NationalVocabularyAccessRight;

class NationalVocabularyAccessRightTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessRightShouldReturnCorrectLabel()
    {
        $AccessRight = new NationalVocabularyAccessRight();

        $this->assertSame('Gestion des vocabulaires Nationaux dédiés aux CRFCB', $AccessRight->getLabel());
    }

    public function testAccessRightShouldSupportUserClass()
    {
        $AccessRight = new NationalVocabularyAccessRight();

        $this->assertSame(false, $AccessRight->supportsClass('Foo\Bar\Class'));
        $this->assertSame(false, $AccessRight->supportsClass('Sygefor\Bundle\CoreBundle\Tests\Entity\MyOrganizationVocabulary'));
        $this->assertSame(true, $AccessRight->supportsClass('Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface'));
        $this->assertSame(true, $AccessRight->supportsClass('Sygefor\Bundle\CoreBundle\Tests\Entity\MyNationalVocabulary'));
    }

    public function testAccessRightIsGrantedToUser()
    {
        $AccessRight = new NationalVocabularyAccessRight();

        $org1 = 'fooOrg';

        $user1 = $this->getMock('Sygefor\Bundle\CoreBundle\Entity\User\User');
        $user1->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org1));

        $token1 = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token1->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user1));

        //mocking User object although type is of no importance for current test
        $object = $this->getMock('Sygefor\Bundle\CoreBundle\Tests\Entity\MyNationalVocabulary');
        $object->expects($this->any())
            ->method('isNational')
            ->will($this->returnValue(true));

        $object2 = $this->getMock('Sygefor\Bundle\CoreBundle\Tests\Entity\MyNationalVocabulary');
        $object2->expects($this->any())
            ->method('isNational')
            ->will($this->returnValue(false));

        $this->assertSame(true, $AccessRight->isGranted($token1, $object, null));
        $this->assertSame(false, $AccessRight->isGranted($token1, $object2, null));
        $this->assertSame(true, $AccessRight->isGranted($token1, null, null));
        $this->assertSame(true, $AccessRight->isGranted($token1, 'Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface', null));
    }
}
