<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 17/07/14
 * Time: 11:51.
 */
namespace Sygefor\Bundle\TrainerBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\CoreBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AllTrainerUpdateAccessRights extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Modification des formateurs de tous les centres';
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
        if ($attribute !== 'EDIT') return false;

        return true;
    }
}
