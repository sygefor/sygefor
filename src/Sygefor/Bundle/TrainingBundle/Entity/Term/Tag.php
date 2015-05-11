<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractOrganizationTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface;

/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity
 */
class Tag extends AbstractOrganizationTerm implements LocalVocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return "Tags";
    }

    /**
     * @return mixed
     */
    public static function orderBy()
    {
        return 'name';
    }
}
