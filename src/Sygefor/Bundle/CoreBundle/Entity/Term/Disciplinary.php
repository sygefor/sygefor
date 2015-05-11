<?php

namespace Sygefor\Bundle\CoreBundle\Entity\Term;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Theme
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="disciplinary")
 * @ORM\Entity
 */
class Disciplinary extends AbstractTerm implements NationalVocabularyInterface
{
    use TreeTrait;

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return "Disciplines";
    }
}
