<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 26/03/14
 * Time: 11:25
 */

namespace Sygefor\Bundle\TaxonomyBundle\Vocabulary;


interface LocalVocabularyInterface extends NationalVocabularyInterface
{
    /**
     * @return mixed
     */
    public function getOrganization();

    /**
     * @param String $organization
     */
    public function setOrganization($organization);

}
