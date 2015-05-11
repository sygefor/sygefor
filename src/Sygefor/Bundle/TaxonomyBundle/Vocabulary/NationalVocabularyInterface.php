<?php
namespace Sygefor\Bundle\TaxonomyBundle\Vocabulary;

/**
 * Interface VocabularyInterface
 * @package Sygefor\Bundle\TaxonomyBundle\Vocabulary
 */
interface NationalVocabularyInterface
{
    /**
     * @return boolean
     */
    public function isNational();

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
