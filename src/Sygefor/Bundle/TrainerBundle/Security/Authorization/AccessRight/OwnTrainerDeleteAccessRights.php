<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 17/07/14
 * Time: 11:50.
 */
namespace Sygefor\Bundle\TrainerBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\CoreBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OwnTrainerDeleteAccessRights extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Supprimer les formateurs de son propre centre';
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
        if ($class === 'Sygefor\Bundle\TrainerBundle\Entity\AbstractTrainer') {
            return true;
        }

        return false;
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($attribute !== 'DELETE') return false;
        if ($object) {
            return $object->getOrganization() === $token->getUser()->getOrganization();
        } else {
            return true;
        }
    }
}
