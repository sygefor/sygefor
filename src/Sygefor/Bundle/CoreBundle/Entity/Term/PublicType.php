<?php

namespace Sygefor\Bundle\CoreBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * Theme
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="public_type")
 * @ORM\Entity
 */
class PublicType extends AbstractTerm implements NationalVocabularyInterface
{
    use TreeTrait;

    /**
     * @ORM\Column(name="priority", type="boolean")
     * @Serializer\Groups({"api"})
     */
    protected $priority;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Term\PublicType")
     * @ORM\JoinColumn(name="legacy_id", referencedColumnName="id")
     * @Serializer\Exclude
     */
    protected $legacyPublicType;

    /**
     * Constructor
     */
    public function __construct() {
        $this->targeted = false;
    }

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return "Catégories de public";
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getLegacyPublicType()
    {
        return $this->legacyPublicType;
    }

    /**
     * @param mixed $legacyPublicType
     */
    public function setLegacyPublicType($legacyPublicType)
    {
        $this->legacyPublicType = $legacyPublicType;
    }

    /**
     * returns the form type name for template edition
     * @return string
     */
    public static function getFormType()
    {
        return 'publictype';
    }
}
