<?php
namespace Sygefor\Bundle\TraineeBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Status de présense
 *
 * @ORM\Table(name="presence_status")
 * @ORM\Entity
 */
class PresenceStatus extends AbstractTerm implements NationalVocabularyInterface
{
    const STATUS_ABSENT = 0;
    const STATUS_PRESENT = 1;

    /**
     * @var integer
     * @ORM\Column(name="status", type="integer")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $status = self::STATUS_ABSENT;

    /**
     * @param int $status
     */
    public function __construct($status = self::STATUS_ABSENT) {
        $this->setStatus($status);
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getVocabularyName()
    {
        return "Statut de présence";
    }
}
