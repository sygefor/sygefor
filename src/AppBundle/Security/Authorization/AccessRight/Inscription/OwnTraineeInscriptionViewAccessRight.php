<?php
/**
 * Created by PhpStorm.
 * User: jetbrains
 * Date: 12/8/17
 * Time: 10:25 AM.
 */

namespace AppBundle\Security\Authorization\AccessRight\Inscription;

use Sygefor\Bundle\CoreBundle\Entity\AbstractInscription;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Sygefor\Bundle\CoreBundle\Security\Authorization\AccessRight\AbstractAccessRight;

/**
 * Class OwnTraineeInscriptionViewAccessRight.
 */
class OwnTraineeInscriptionViewAccessRight extends AbstractAccessRight
{
    protected $supportedClass = AbstractInscription::class;
    protected $supportedOperation = 'VIEW';

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Voir les inscriptions des stagiaires de son propre centre';
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($attribute !== $this->supportedOperation) {
            return false;
        }
        if ($object) {
            return $object->getTrainee() && $object->getTrainee()->getOrganization()->getId() === $token->getUser()->getOrganization()->getId();
        }

        return true;
    }
}
