<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 20/03/14
 * Time: 15:42.
 */
namespace Sygefor\Bundle\CoreBundle\Security\Authorization\AccessRight\User;

use Sygefor\Bundle\CoreBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OwnOrganizationUserAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Gestion des utilisateurs de son propre centre';
    }

    /**
     * Checks if the access right supports the given class.
     *
     * @param string
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return 'Sygefor\Bundle\CoreBundle\Entity\User\User' === $class;
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($object) {
            return $object->getOrganization() === $token->getUser()->getOrganization();
        }
        else {
            return true;
        }
    }
}
