<?php

namespace Sygefor\Bundle\TaxonomyBundle\Security\Authorization\AccessRight;

use Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NationalVocabularyAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Gestion des vocabulaires nationaux';
    }

    /**
     * Checks if the access right supports the given class.
     *
     * @param string
     * @return Boolean
     */
    public function supportsClass($class)
    {

        if ($class == 'Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface') {
            return true;
        }
        try {
            $refl = new \ReflectionClass($class);
            return $refl ? ( $refl->isSubclassOf('Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface') && !$refl->isSubclassOf('Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface') && ('Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface' != $class)) : false;
        } catch (\ReflectionException $re){
            return false;
        }

    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {

        if (is_string($object) ) {
            return true;
        } else if ($object) {
            return ($object->isNational());
        } else {
            return true;
        }

    }


}
