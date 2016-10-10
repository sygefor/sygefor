<?php

namespace Sygefor\Bundle\CoreBundle\Tests\Vocabulary;

use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyRegistry;

/**
 * Class VocabularyRegistryTest.
 */
class VocabularyRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testAddVocabulary()
    {
        $registry = new VocabularyRegistry();

        $vocabulary1 = $this->getMock('Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface');
        $registry->addVocabulary($vocabulary1, 'sygefor_core.vocabulary_provider.foo', 'group');

        $vocabulary2 = $this->getMock('Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface');
        $registry->addVocabulary($vocabulary2, 'sygefor_core.vocabulary_provider.bar', 'group');

        $vocabularies      = $registry->getVocabularies();
        $groups            = $registry->getGroups();
        $vocabularyFromGet = $registry->getVocabularyById('sygefor_core.vocabulary_provider.bar');

        $this->assertSame(2, count($vocabularies));
        $this->assertSame(1, count($groups));
        $this->assertSame($vocabulary2, $vocabularyFromGet);
    }
}
