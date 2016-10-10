<?php

namespace Sygefor\Bundle\InstitutionBundle\Entity\Term;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;

/**
 * GeographicOrigin.
 *
 * @ORM\Table(name="geographic_origin")
 * @ORM\Entity
 * Traduction: Origine Géographique
 */
class GeographicOrigin extends AbstractTerm implements VocabularyInterface
{
    /**
     * This term is required during term replacement.
     *
     * @var bool
     */
    static $replacementRequired = true;

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return 'Origine Géographique';
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
