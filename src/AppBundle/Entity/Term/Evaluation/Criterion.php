<?php

namespace AppBundle\Entity\Term\Evaluation;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Form\Type\CriterionType;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Entity\Term\VocabularyInterface;

/**
 * Critère d'évaluation.
 *
 * @ORM\Table(name="evaluation_criterion")
 * @ORM\Entity
 */
class Criterion extends AbstractTerm implements VocabularyInterface
{
    /**
     * @var Theme
     * @ORM\ManyToOne(targetEntity="Theme", inversedBy="criteria")
     * @Serializer\Exclude
     */
    protected $theme;

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param Theme $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return string
     */
    public function getVocabularyName()
    {
        return "Critère d'évaluation";
    }

    /**
     * @return mixed
     *               This static method is used to set a specific order field
     *               when fetch terms
     */
    public static function orderBy()
    {
        return 'position';
    }

    public static function getFormType()
    {
        return CriterionType::class;
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
