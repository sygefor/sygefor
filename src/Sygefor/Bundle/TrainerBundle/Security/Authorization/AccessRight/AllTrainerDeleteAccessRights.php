<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 17/07/14
 * Time: 11:51
 */
namespace Sygefor\Bundle\TrainerBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AllTrainerDeleteAccessRights extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Suppression des formateurs de toutes les URFIST';
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
        if ($attribute != 'DELETE') return false;
        return true;
    }
} 