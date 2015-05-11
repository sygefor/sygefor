<?php

namespace Sygefor\Bundle\TaxonomyBundle\Tests\Vocabulary;

use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyRegistry;

/**
 * Class VocabularyRegistryTest
 * @package Sygefor\Bundle\TaxonomyBundle\Tests\Vocabulary
 */
class VocabularyRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testAddVocabulary()
    {
        $registry = new VocabularyRegistry();

        $vocabulary1 = $this->getMock('Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface');
        $registry->addVocabulary($vocabulary1, "sygefor_taxonomy.vocabulary_provider.foo", "group");

        $vocabulary2 = $this->getMock('Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface');
        $registry->addVocabulary($vocabulary2, "sygefor_taxonomy.vocabulary_provider.bar", "group");

        $vocabularies = $registry->getVocabularies();
        $groups = $registry->getGroups();
        $vocabularyFromGet = $registry->getVocabularyById("sygefor_taxonomy.vocabulary_provider.bar");

        $this->assertEquals(2, count($vocabularies));
        $this->assertEquals(1, count($groups));
        $this->assertEquals($vocabulary2,$vocabularyFromGet);
    }
}
