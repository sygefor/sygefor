<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 20/03/14
 * Time: 16:46
 */

namespace Sygefor\Bundle\TrainingBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AllTrainingUpdateAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Modifier les formations de toutes les URFIST';
    }

    /**
     * Checks if the access right supports the given class.
     *
     * @param string
     * @return Boolean
     */
    public function supportsClass($class)
    {
        if ($class == 'Sygefor\Bundle\TrainingBundle\Entity\Training' || $class == 'Sygefor\Bundle\TrainingBundle\Entity\Session') {
            return true;
        }
        try {
            $refl = new \ReflectionClass($class);
            return $refl->isSubclassOf('Sygefor\Bundle\TrainingBundle\Entity\Training');
        } catch (\ReflectionException $re){
            return false;
        }
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($attribute != 'EDIT') return false;
        return true;
    }
}
