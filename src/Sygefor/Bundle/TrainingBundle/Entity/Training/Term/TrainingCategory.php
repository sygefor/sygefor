<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Training\Term;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;
use Sygefor\Bundle\TrainingBundle\Form\TrainingCategoryType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Catégorie de formation.
 *
 * @ORM\Table(name="training_category")
 * @ORM\Entity
 */
class TrainingCategory extends AbstractTerm implements VocabularyInterface
{
    /**
     * @var string
     * @ORM\Column(name="trainingType", type="string", length=256, nullable=true)
     * @Assert\NotNull(message="Vous devez renseigner un type de formation")
     * @Serializer\Groups({"Default", "api"})
     */
    private $trainingType;

    /**
     * @return string
     */
    public function getTrainingType()
    {
        return $this->trainingType;
    }

    /**
     * @param string $trainingType
     */
    public function setTrainingType($trainingType)
    {
        $this->trainingType = $trainingType;
    }

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return 'Catégorie de formation';
    }

    public static function getFormType()
    {
        return TrainingCategoryType::class;
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
