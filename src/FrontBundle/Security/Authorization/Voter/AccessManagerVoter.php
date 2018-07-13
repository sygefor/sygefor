<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 8/8/17
 * Time: 4:23 PM.
 */

namespace FrontBundle\Security\Authorization\Voter;

use AppBundle\Entity\Trainee\Trainee;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AccessManagerVoter.
 */
class AccessManagerVoter implements VoterInterface
{
    protected $attributes = [
        'MANAGE',
        'VALIDATOR',
        'COFO',
    ];

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, $this->attributes);
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === Trainee::class;
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
                // the current token must have a User
                if (!($token->getUser() instanceof Trainee)) {
                    return VoterInterface::ACCESS_DENIED;
                }

                /** @var Trainee $trainee */
                $trainee = $token->getUser();
                $isValidator = $token->getUser()->isValidator();
                $isCofo = $token->getUser()->isCofo();
                $isManager = false;
                if ($trainee->getInstitution() && $trainee->getInstitution()->getManager()) {
                    $isManager = $trainee->getInstitution()->getManager()->getId() === $trainee->getId();
                }
                $isValidator = $isValidator || $isManager;
                switch ($attribute) {
                    case 'MANAGE':
                        return $isValidator || $isManager || $isCofo;
                    case 'VALIDATOR':
                        return $isValidator || $isManager;
                    case 'COFO':
                        return $isCofo;
                    default:
                        return VoterInterface::ACCESS_DENIED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
