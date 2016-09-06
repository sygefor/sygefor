<?php
namespace Sygefor\Bundle\TaxonomyBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;

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
    public function getVocabularyName(){
        return "Vocabulaire National 1";
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
