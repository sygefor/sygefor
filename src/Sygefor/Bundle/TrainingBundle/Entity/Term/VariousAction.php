<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;

/**
 * OrganizationTrainingVocabulary
 *
 * @ORM\Table(name="various_action")
 * @ORM\Entity
 * traduction: actions diverses
 *
 */
class VariousAction extends AbstractTerm implements VocabularyInterface
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
        return "Type d'action diverse";
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_LOCAL;
    }

    function __toString()
    {
        return $this->getName();
    }


}
