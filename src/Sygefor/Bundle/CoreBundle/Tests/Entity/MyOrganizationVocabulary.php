<?php

namespace Sygefor\Bundle\CoreBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;

/**
 * OrganizationVocabulary.
 *
 * @ORM\Table(name="test_organization_vocabulary")
 * @ORM\Entity
 */
class MyOrganizationVocabulary extends AbstractTerm implements VocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName() {
        return 'Vocabulaire 2 dedie a un CRFCB';
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_LOCAL;
    }
}
