<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 17/07/14
 * Time: 11:50
 */
namespace Sygefor\Bundle\TrainerBundle\Security\Authorization\AccessRight;


use Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OwnTrainerViewAccessRights extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return "Voir les formateurs de sa propre URFIST";
    }

    /**
     * Checks if the access right supports the given class.
     *
     * @param string
     * @return Boolean
     */
    public function supportsClass($class)
    {
        if ($class == 'Sygefor\Bundle\TrainerBundle\Entity\Trainer') {
            return true;
        }
        return false;
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($attribute != 'VIEW') return false;
        if ($object) {
            return ($object->getOrganization() == $token->getUser()->getOrganization());
        } else {
            return true;
        }
    }
} 