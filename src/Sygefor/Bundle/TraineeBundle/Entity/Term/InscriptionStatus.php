<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 27/05/14
 * Time: 17:44
 */

namespace Sygefor\Bundle\TraineeBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Statut de l'inscription
 *
 * @ORM\Table(name="inscription_status")
 * @ORM\Entity
 */
class InscriptionStatus extends AbstractTerm implements NationalVocabularyInterface
{
    const STATUS_PENDING = 0;
    const STATUS_WAITING = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_REJECTED = 3;

    /**
     * @var integer
     * @ORM\Column(name="status", type="integer")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $status = self::STATUS_PENDING;

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
        return "Statut de l'inscription";
    }
}
