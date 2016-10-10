<?php
namespace Sygefor\Bundle\MyCompanyBundle\Entity\Term;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Critère d'évaluation
 *
 * @ORM\Table(name="evaluation_criterion")
 * @ORM\Entity
 */
class EvaluationCriterion extends AbstractTerm implements VocabularyInterface
{
    /**
     * @return string
     */
    public function getVocabularyName()
    {
        return "Critère d'évaluation";
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
