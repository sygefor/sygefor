<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 20/03/14
 * Time: 15:42
 */

namespace Sygefor\Bundle\UserBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Sygefor\Bundle\UserBundle\Entity\User;

class OwnOrganizationUserAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return "Gestion des utilisateurs de sa propre URFIST";
    }

    /**
     * Checks if the access right supports the given class.
     *
     * @param string
     * @return Boolean
     */
    public function supportsClass($class)
    {
        return ('Sygefor\Bundle\UserBundle\Entity\User' == $class);
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($object) {
            return ($object->getOrganization() == $token->getUser()->getOrganization());
        } else {
            return true;
        }
    }
}
