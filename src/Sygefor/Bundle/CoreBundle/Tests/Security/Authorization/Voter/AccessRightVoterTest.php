<?php

namespace Sygefor\Bundle\CoreBundle\Tests\Security\Voter;

use Sygefor\Bundle\CoreBundle\AccessRight\AccessRightRegistry;
use Sygefor\Bundle\CoreBundle\Entity\User\User;
use Sygefor\Bundle\CoreBundle\Security\Authorization\Voter\AccessRightVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class AccessRightVoterTest.
 */
class AccessRightVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    private $registry;

    /**
     * setUp.
     */
    public function setUp()
    {
        $this->registry = new AccessRightRegistry($this->getContainerMock());
        $this->setUpAccessRights();
        $this->voter = new AccessRightVoter($this->registry);
    }

    /**
     * setUpAccessRights.
     */
    public function setUpAccessRights()
    {
        $this->registry = new AccessRightRegistry($this->getContainerMock());

        $accessRight = $this->getMock('Sygefor\Bundle\CoreBundle\AccessRight\AbstractAccessRight');
        $accessRight->expects($this->any())->method('supportsClass')->will($this->returnCallback(function ($class) { return $class === 'Foo'; }));
        $accessRight->expects($this->any())->method('supportsAttribute')->will($this->returnCallback(function ($attribute) { return $attribute === 'EDIT'; }));
        $accessRight->expects($this->any())->method('isGranted')->will($this->returnValue(true));
        $this->registry->addAccessRight('edit.foo', $accessRight);

        $accessRight = $this->getMock('Sygefor\Bundle\CoreBundle\AccessRight\AbstractAccessRight');
        $accessRight->expects($this->any())->method('supportsClass')->will($this->returnCallback(function ($class) { return $class === 'Bar'; }));
        $accessRight->expects($this->any())->method('supportsAttribute')->will($this->returnCallback(function ($attribute) { return $attribute === 'REMOVE'; }));
        $accessRight->expects($this->any())->method('isGranted')->will($this->returnValue(true));
        $this->registry->addAccessRight('remove.bar', $accessRight);
    }

    /**
     * testSupportsClass.
     */
    public function testSupportsClass()
    {
        $this->assertTrue($this->voter->supportsClass('Foo'));
    }

    /**
     * @dataProvider getVoteTests
     */
    public function testVote($userRights, $object, $attributes, $expected)
    {
        $this->assertSame($expected, $this->voter->vote($this->getToken($userRights), $object, $attributes));
    }

    /**
     * @return array
     */
    public function getVoteTests()
    {
        $foo = $this->getMockBuilder('nonexistant')->setMockClassName('Foo')->getMock();
        $bar = $this->getMockBuilder('nonexistant')->setMockClassName('Bar')->getMock();

        return array(
            array(array(''), 'Foo', array('EDIT'), VoterInterface::ACCESS_ABSTAIN),
            array(array('edit.foo'), 'Foo', array('EDIT'), VoterInterface::ACCESS_GRANTED),
            array(array('edit.foo'), $foo, array('EDIT'), VoterInterface::ACCESS_GRANTED),
            array(array('edit.foo'), 'Foo', array('REMOVE'), VoterInterface::ACCESS_ABSTAIN),
            array(array('remove.bar'), 'Bar', array('REMOVE'), VoterInterface::ACCESS_GRANTED),
            array(array('remove.bar'), $bar, array('REMOVE'), VoterInterface::ACCESS_GRANTED),
        );
    }

    /**
     * @param $userRights
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getToken($userRights)
    {
        $user = $this->getMock('Sygefor\Bundle\CoreBundle\Entity\User\User');
        $user->expects($this->any())
          ->method('getAccessRights')
          ->will($this->returnValue($userRights));

        $user->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue( array('ROLE_ADMIN')));

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())
          ->method('getUser')
          ->will($this->returnValue($user));

        return $token;
    }

    public function getContainerMock()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        return $container;
    }

    public function getUserRights()
    {
        $userRights = array(
            'sygefor_training.rights.training.all',
            'sygefor_core.rights.user.all',
            'sygefor_core.rights.user.own',
            'sygefor_core.rights.group',
            'sygefor_core.rights.vocabulary.all',
            'sygefor_core.rights.vocabulary.own',
            'sygefor_core.rights.vocabulary.national',
            'sygefor_training.rights.training.own',
        );

        return $userRights;
    }
}
