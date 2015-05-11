<?php
namespace Sygefor\Bundle\UserBundle\Tests\Security\Voter ;

use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Sygefor\Bundle\UserBundle\Entity\User;
use Sygefor\Bundle\UserBundle\Security\Authorization\Voter\AccessRightVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class AccessRightVoterTest
 * @package Sygefor\Bundle\UserBundle\Tests\Security\Voter
 */
class AccessRightVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    private $registry;


    /**
     * setUp
     */
    public function setUp()
    {
        $this->registry = new AccessRightRegistry($this->getContainerMock());
        $this->setUpAccessRights();
        $this->voter = new AccessRightVoter($this->registry);
    }

    /**
     * setUpAccessRights
     */
    public function setUpAccessRights()
    {
        $this->registry = new AccessRightRegistry($this->getContainerMock());

        $accessRight = $this->getMock('Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight');
        $accessRight->expects($this->any())->method('supportsClass')->will($this->returnCallback(function($class) { return ($class == 'Foo'); }));
        $accessRight->expects($this->any())->method('supportsAttribute')->will($this->returnCallback(function($attribute) { return ($attribute == 'EDIT'); }));
        $accessRight->expects($this->any())->method('isGranted')->will($this->returnValue(true));
        $this->registry->addAccessRight('edit.foo', $accessRight);

        $accessRight = $this->getMock('Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight');
        $accessRight->expects($this->any())->method('supportsClass')->will($this->returnCallback(function($class) { return ($class == 'Bar'); }));
        $accessRight->expects($this->any())->method('supportsAttribute')->will($this->returnCallback(function($attribute) { return ($attribute == 'REMOVE'); }));
        $accessRight->expects($this->any())->method('isGranted')->will($this->returnValue(true));
        $this->registry->addAccessRight('remove.bar', $accessRight);
    }

    /**
     * testSupportsClass
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
            array(array('remove.bar'), $bar, array('REMOVE'), VoterInterface::ACCESS_GRANTED)
        );
    }

    /**
     * @param $userRights
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getToken($userRights)
    {
        $user = $this->getMock('Sygefor\Bundle\UserBundle\Entity\User');
        $user->expects($this->any())
          ->method('getAccessRights')
          ->will($this->returnValue($userRights));

        $user->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue( array ('ROLE_ADMIN')));

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
            'sygefor_user.rights.user.all',
            'sygefor_user.rights.user.own',
            'sygefor_user.rights.group',
            'sygefor_taxonomy.rights.vocabulary.all',
            'sygefor_taxonomy.rights.vocabulary.own',
            'sygefor_taxonomy.rights.vocabulary.national',
            'sygefor_training.rights.training.own',
        );
        return $userRights;
    }
}
