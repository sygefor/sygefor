<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * TeachingCursus
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="teaching_cursus")
 * @ORM\Entity
 */
class TeachingCursus extends AbstractTerm implements NationalVocabularyInterface
{
    use TreeTrait;

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return "Cursus d'enseignement";
    }
}
