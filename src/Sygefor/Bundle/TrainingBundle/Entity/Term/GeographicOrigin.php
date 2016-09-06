<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;

/**
 * GeographicOrigin
 *
 * @ORM\Table(name="geographic_origin")
 * @ORM\Entity
 * traduction: actions diverses
 *
 */
class GeographicOrigin extends AbstractTerm implements VocabularyInterface
{
    /**
     * This term is required during term replacement
     * @var bool
     */
    static $replacementRequired = true;

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return "Origine Géographique";
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
