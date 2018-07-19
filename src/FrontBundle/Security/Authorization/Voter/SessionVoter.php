<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 6/15/18
 * Time: 9:43 AM.
 */

namespace FrontBundle\Security\Authorization\Voter;

use Sygefor\Bundle\CoreBundle\Entity\AbstractSession;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class SessionVoter.
 */
class SessionVoter implements VoterInterface
{
    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        return $attribute === 'VIEW';
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return get_parent_class($class) === AbstractSession::class;
    }

    /**
     * Vote to decide access on a particular object.
     *
     * @param TokenInterface $token
     * @param object         $object
     * @param array          $attributes
     *
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {
                // the current token must have a Trainee
                if (get_parent_class($token->getUser()) !== AbstractTrainee::class) {
                    if ($token instanceof AnonymousToken) {
                        return VoterInterface::ACCESS_GRANTED;
                    }

                    return VoterInterface::ACCESS_ABSTAIN;
                }

                if ($token->getUser()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_ABSTAIN;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
