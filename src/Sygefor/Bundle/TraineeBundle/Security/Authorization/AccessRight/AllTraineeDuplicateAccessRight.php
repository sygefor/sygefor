<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 20/03/14
 * Time: 16:46
 */

namespace Sygefor\Bundle\TraineeBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AllTraineeDuplicateAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Gestion des doublons de stagiaires de toutes les URFIST';
    }

    /**
     * Checks if the access right supports the given class.
     *
     * @param string
     * @return Boolean
     */
    public function supportsClass($class)
    {
        if ($class == 'Sygefor\Bundle\TraineeBundle\Entity\Trainee') {
            return true;
        }
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($attribute != 'MANAGEDUPLICATE') return false;
        return true;
    }
}
