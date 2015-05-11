<?php
namespace Sygefor\Bundle\UserBundle\Security\Authorization\Voter;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Sygefor\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class AccessRightVoter
 * @package Sygefor\Bundle\UserBundle\Security\Authorization\Voter
 */
class AccessRightVoter implements VoterInterface
{
    /**
     * @var AccessRightRegistry
     */
    private $registry;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Construct
     */
    function __construct(AccessRightRegistry $registry, EntityManager $entityManager = null)
    {
        $this->registry = $registry;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        return true;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * Vote to decide access on a particular object
     *
     * @param TokenInterface $token
     * @param object $object
     * @param array $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        // the current token must have a User
        if(!($token->getUser() instanceof User)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // support of Doctrine namespace alias
        if(is_string($object) && strpos($object, ":") && $this->entityManager) {
            list($alias, $class) = explode(":", $object);
            $namespace = $this->entityManager->getConfiguration()->getEntityNamespace($alias);
            $object = $namespace . '\\' . $class;
        }

        // Run overs user access rights
        foreach($attributes as $attribute) {
            foreach($token->getUser()->getAccessRights() as $accessRightId) {
                //$className = is_string($object) ? $object : get_class($object);
                $className = is_string($object) ? $object : ClassUtils::getRealClass(get_class($object));
                $accessRight = $this->registry->getAccessRightById($accessRightId);
                if($accessRight && $accessRight->supportsClass($className) && $accessRight->supportsAttribute($attribute)) {
                    if($accessRight->isGranted($token, is_object($object) ? $object : null, $attribute)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }
            }
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }

}
