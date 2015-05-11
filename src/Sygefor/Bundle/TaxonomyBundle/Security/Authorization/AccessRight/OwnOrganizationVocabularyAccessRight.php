<?php

namespace Sygefor\Bundle\TaxonomyBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Sygefor\Bundle\UserBundle\Entity\User;

class OwnOrganizationVocabularyAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return "Gestion des vocabulaires locaux de sa propre URFIST";
    }

    /**
     * Checks if the access right supports the given class.
     *
     * @param string
     * @return Boolean
     */
    public function supportsClass($class)
    {

        if ($class == 'Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface') {

            return true;
        }
        try {
            $refl = new \ReflectionClass($class);

            return $refl ? $refl->isSubclassOf('Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface') : false;
        } catch (\ReflectionException $re){
            return false;
        }

    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if ($object) {
            return ($object->getOrganization() == $token->getUser()->getOrganization());
        } else {
            return true;
        }
    }


}
