<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 20/03/14
 * Time: 16:46.
 */
namespace Sygefor\Bundle\TrainingBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\CoreBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AllTrainingViewAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Voir les formations de tous les centres';
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
        if ($class === 'Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining'
            || $class === 'Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession'
            || $class === 'Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining'
        ) {
            return true;
        }
        try {
            $refl = new \ReflectionClass($class);

            return $refl->isSubclassOf('Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining');
        } catch (\ReflectionException $re){
            return false;
        }
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($attribute !== 'VIEW') return false;

        return true;
    }
}
