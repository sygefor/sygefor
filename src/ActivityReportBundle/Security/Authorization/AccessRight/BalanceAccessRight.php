<?php

namespace ActivityReportBundle\Security\Authorization\AccessRight;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Sygefor\Bundle\CoreBundle\Security\Authorization\AccessRight\AbstractAccessRight;

class BalanceAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Accéder aux bilans';
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
        return 'Sygefor\Bundle\UserBundle\Entity\User' === $class;
    }

    public function supportsAttribute($attribute)
    {
        return $attribute === 'BALANCE';
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($this->supportsAttribute($attribute)) {
            return true;
        }

        return false;
    }
}
