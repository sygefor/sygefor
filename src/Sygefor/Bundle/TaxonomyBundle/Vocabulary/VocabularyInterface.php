<?php
namespace Sygefor\Bundle\TaxonomyBundle\Vocabulary;
use Sygefor\Bundle\CoreBundle\Entity\Organization;

/**
 * Interface VocabularyInterface
 * @package Sygefor\Bundle\TaxonomyBundle\Vocabulary
 */
interface VocabularyInterface
{
    const VOCABULARY_NATIONAL = 0;
    const VOCABULARY_LOCAL = 1;
    const VOCABULARY_MIXED = 2;

    /**
     * @return boolean
     */
    public static function getVocabularyStatus();

    /**
     * @return Organization|null mixed
     */
    public function getOrganization();

    /**
     * @param Organization $organization
     */
    public function setOrganization($organization);

    /**
     * @return mixed
     */
    public function getVocabularyId();

    /**
     * @param string $id
     */
    public function setVocabularyId($id);

    /**
     * @return mixed
     */
    public function getVocabularyName();

}
