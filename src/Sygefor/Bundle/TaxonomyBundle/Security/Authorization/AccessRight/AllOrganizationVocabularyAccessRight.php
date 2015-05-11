<?php

namespace Sygefor\Bundle\TaxonomyBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AllOrganizationVocabularyAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Gestion des vocabulaires locaux de toutes les URFIST';
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
            return $refl->isSubclassOf('Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface');
        } catch (\ReflectionException $re){
            return false;
        }

    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        return true;
    }


}
