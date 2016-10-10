<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 20/03/14
 * Time: 16:46.
 */
namespace Sygefor\Bundle\InstitutionBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\CoreBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AllInstitutionCreateAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Créer les établissements de tous les centres';
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
        if ($class === 'Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution') {
            return true;
        }

        return false;
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($attribute !== 'CREATE') {
            return false;
        }

        return true;
    }
}
