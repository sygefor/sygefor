<?php

namespace Sygefor\Bundle\CoreBundle\Entity\Term;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Theme
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="disciplinary")
 * @ORM\Entity
 */
class Disciplinary extends AbstractTerm implements VocabularyInterface
{
    use TreeTrait;

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
        return "Disciplines";
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
