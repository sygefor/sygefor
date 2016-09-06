<?php

namespace Sygefor\Bundle\TaxonomyBundle\Tests\Security\Authorization\AccessRight;

use Sygefor\Bundle\TaxonomyBundle\Security\Authorization\AccessRight\NationalVocabularyAccessRight;

class NationalVocabularyAccessRightTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessRightShouldReturnCorrectLabel()
    {
        $AccessRight = new NationalVocabularyAccessRight();

        $this->assertEquals("Gestion des vocabulaires Nationaux dédiés aux URFIST",$AccessRight->getLabel());
    }

    public function testAccessRightShouldSupportUserClass()
    {
        $AccessRight = new NationalVocabularyAccessRight();

        $this->assertEquals(false,$AccessRight->supportsClass('Foo\Bar\Class'));
        $this->assertEquals(false,$AccessRight->supportsClass('Sygefor\Bundle\TaxonomyBundle\Tests\Entity\MyOrganizationVocabulary'));
        $this->assertEquals(true,$AccessRight->supportsClass('Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface'));
        $this->assertEquals(true,$AccessRight->supportsClass('Sygefor\Bundle\TaxonomyBundle\Tests\Entity\MyNationalVocabulary'));
    }

    public function testAccessRightIsGrantedToUser()
    {
        $AccessRight = new NationalVocabularyAccessRight();

        $org1 = 'fooOrg';

        $user1 = $this->getMock('Sygefor\Bundle\UserBundle\Entity\User');
        $user1->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org1));

        $token1 = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token1->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user1));

        //mocking User object although type is of no importance for current test
        $object = $this->getMock('Sygefor\Bundle\TaxonomyBundle\Tests\Entity\MyNationalVocabulary');
        $object->expects($this->any())
            ->method('isNational')
            ->will($this->returnValue(true));

        $object2 = $this->getMock('Sygefor\Bundle\TaxonomyBundle\Tests\Entity\MyNationalVocabulary');
        $object2->expects($this->any())
            ->method('isNational')
            ->will($this->returnValue(false));

        $this->assertEquals(true, $AccessRight->isGranted($token1, $object, null));
        $this->assertEquals(false, $AccessRight->isGranted($token1, $object2, null));
        $this->assertEquals(true, $AccessRight->isGranted($token1, null, null));
        $this->assertEquals(true, $AccessRight->isGranted($token1, 'Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface', null));

    }

}
