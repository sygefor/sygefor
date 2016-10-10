<?php

namespace Sygefor\Bundle\CoreBundle\Security\Authorization\AccessRight\Vocabulary;

use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;
use Sygefor\Bundle\CoreBundle\AccessRight\AbstractAccessRight;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AllOrganizationVocabularyAccessRight extends AbstractAccessRight
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Gestion des vocabulaires locaux de tous les centres';
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
        if ($class === 'Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface') {
            return true;
        }

        try {
            $refl = new \ReflectionClass($class);

            return $refl->isSubclassOf('Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface');
        }
        catch (\ReflectionException $re) {
            return false;
        }

        return false;
    }

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute)
    {
        if (is_string($object)) {
            return true;
        }
        else if ($object) {
            return $object->getVocabularyStatus() !== VocabularyInterface::VOCABULARY_NATIONAL && $object->getOrganization();
        }
        else {
            return true;
        }
    }
}
