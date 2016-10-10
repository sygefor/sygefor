<?php

namespace Sygefor\Bundle\CoreBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;

/**
 * MyNationalVocabulary, class for testing purposes, to be removed.
 *
 * @ORM\Table(name="test_national_vocabulary")
 * @ORM\Entity
 */
class MyNationalVocabulary extends AbstractTerm implements VocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName() {
        return 'Vocabulaire National 1';
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
