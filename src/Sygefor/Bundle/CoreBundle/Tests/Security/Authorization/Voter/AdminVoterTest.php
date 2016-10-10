<?php

namespace Sygefor\Bundle\CoreBundle\Tests\Security\Voter;

use Sygefor\Bundle\CoreBundle\Security\Authorization\Voter\AdminVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Class AdminVoterTest.
 */
class AdminVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testSupportsClass.
     */
    public function testSupportsClass()
    {
        $voter = new AdminVoter();
        $this->assertTrue($voter->supportsClass('Foo'));
    }

    /**
     * @dataProvider getVoteTests
     */
    public function testVote($roles, $expected)
    {
        $voter = new AdminVoter();
        $this->assertSame($expected, $voter->vote($this->getToken($roles), null, array()));
    }

    /**
     * @return array
     */
    public function getVoteTests()
    {
        return array(
            array(array(), VoterInterface::ACCESS_ABSTAIN),
            array(array('ROLE_ADMIN'), VoterInterface::ACCESS_GRANTED),
        );
    }

    /**
     * @param array $roles
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getToken(array $roles)
    {
        foreach ($roles as $i => $role) {
            $roles[$i] = new Role($role);
        }
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())
          ->method('getRoles')
          ->will($this->returnValue($roles));

        return $token;
    }
}
