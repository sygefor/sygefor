<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 8/8/17
 * Time: 4:23 PM.
 */

namespace FrontBundle\Security\Authorization\Voter;

use AppBundle\Entity\Trainee\Trainee;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AccessTrainingVoter.
 */
class AccessTrainingVoter implements VoterInterface
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
        return AbstractTraining::class === get_parent_class($class);
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
        if (is_object($object) && $this->supportsClass(get_class($object))) {
            foreach ($attributes as $attribute) {
                if ($this->supportsAttribute($attribute)) {
                    // the current token must have a User
                    if (!is_string($token->getUser()) && !($token->getUser() instanceof Trainee)) {
                        return VoterInterface::ACCESS_ABSTAIN;
                    }

                    return true;
                }
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
