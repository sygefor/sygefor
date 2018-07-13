<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 12/5/17
 * Time: 4:07 PM.
 */

namespace AppBundle\Entity\Term\Evaluation;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\ArrayCollection;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Entity\Term\VocabularyInterface;

/**
 * Thème d'évaluation.
 *
 * @ORM\Table(name="evaluation_theme")
 * @ORM\Entity
 */
class Theme extends AbstractTerm implements VocabularyInterface
{
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Criterion", mappedBy="theme", cascade={"persist", "remove"})
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $criteria;

    public function __construct()
    {
        $this->criteria = new ArrayCollection();
    }

    public function __clone()
    {
        $this->criteria = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param ArrayCollection $criteria
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @return string
     */
    public function getVocabularyName()
    {
        return "Thème d'évaluation";
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

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
