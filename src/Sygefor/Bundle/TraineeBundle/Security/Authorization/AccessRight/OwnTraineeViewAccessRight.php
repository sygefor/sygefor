<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 20/03/14
 * Time: 15:42.
 */
namespace Sygefor\Bundle\TraineeBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\CoreBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OwnTraineeViewAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Voir les stagiaires de son propre centre';
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
        if ($class === 'Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee') {
            return true;
        }
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($attribute !== 'VIEW') return false;

        if ($object) {
            return $object->getOrganization() === $token->getUser()->getOrganization();
        } else {
            return true;
        }
    }
}
