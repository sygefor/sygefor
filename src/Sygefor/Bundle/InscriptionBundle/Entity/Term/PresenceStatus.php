<?php

namespace Sygefor\Bundle\InscriptionBundle\Entity\Term;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;

/**
 * Status de présense.
 *
 * @ORM\Table(name="presence_status")
 * @ORM\Entity
 */
class PresenceStatus extends AbstractTerm implements VocabularyInterface
{
    const STATUS_ABSENT  = 0;
    const STATUS_PRESENT = 1;

    /**
     * This term is required during term replacement.
     *
     * @var bool
     */
    static $replacementRequired = true;

    /**
     * @var int
     * @ORM\Column(name="status", type="integer")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $status = self::STATUS_ABSENT;

    /**
     * @param int $status
     */
    public function __construct($status = self::STATUS_ABSENT)
    {
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

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_MIXED;
    }

    /**
     * @return string
     */
    public function getVocabularyName()
    {
        return 'Statut de présence';
    }

    /**
     * returns the form type name for template edition.
     *
     * @return string
     */
    public static function getFormType()
    {
        return PresenceStatusVocabularyType::class;
    }
}
