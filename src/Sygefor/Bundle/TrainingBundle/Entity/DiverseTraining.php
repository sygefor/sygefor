<?php

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublicType;
use Sygefor\Bundle\TrainingBundle\Form\DiverseTrainingType;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="diverse_training")
 * @ORM\Entity
 * traduction: formations diverses
 */
class DiverseTraining extends Training
{
    /**
     * @var VariousAction
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction")
     * @Serializer\Groups({"training", "api"})
     */
    protected $variousAction;

    /**
     * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Term\PublicType")
     * @ORM\JoinTable(name="diversetrainings__diversetrainings_publictype",
     *      joinColumns={@ORM\JoinColumn(name="diversetrainings_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="public_type_id", referencedColumnName="id")}
     * )
     * @Serializer\Groups({"training", "inscription", "api"})
     */
    protected $publicTypes;

    /**
     * @ORM\Column(name="organism", type="string", length=255, nullable=true)
     * @var String
     * @Serializer\Groups({"training", "api"})
     */
    protected $organism;

    /**
     * @ORM\Column(name="agreement", type="boolean")
     * @var boolean
     * @Serializer\Groups({"training", "api"})
     */
    protected $agreement = false;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->publicTypes = new ArrayCollection();
    }

    public function __clone()
    {
        parent::__clone();
        $this->publicTypes = new ArrayCollection();
    }

    /**
     * @return string
     */
    static public function getType() {
        return 'diverse_training';
    }

    /**
     * @return string
     */
    static public function getTypeLabel()
    {
        return 'Action diverse';
    }

    /**
     * @return string
     */
    static public function getFormType()
    {
        return 'diversetrainingtype';
    }

    /**
     * @param boolean $agreement
     */
    public function setAgreement($agreement)
    {
        $this->agreement = $agreement;
    }

    /**
     * @return boolean
     */
    public function getAgreement()
    {
        return $this->agreement;
    }

    /**
     * @param String $organism
     */
    public function setOrganism($organism)
    {
        $this->organism = $organism;
    }

    /**
     * @return String
     */
    public function getOrganism()
    {
        return $this->organism;
    }

    /**
     * @param mixed $publicTypes
     */
    public function setPublicTypes($publicTypes)
    {
        $this->publicTypes = $publicTypes;
    }

    /**
     * @return mixed
     */
    public function getPublicTypes()
    {
        return $this->publicTypes;
    }

    /**
     * @param $publicType
     */
    public function addPublicType($publicType)
    {
        $this->publicTypes->add($publicType);
    }

    /**
     * @param $publicType
     */
    public function removePublicType($publicType)
    {
        $this->publicTypes->remove($publicType);
    }

    /**
     * @param \Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction $variousAction
     */
    public function setVariousAction($variousAction)
    {
        $this->variousAction = $variousAction;
    }

    /**
     * @return \Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction
     */
    public function getVariousAction()
    {
        return $this->variousAction;
    }

    /**
     * HumanReadablePropertyAccessor helper : provides a list of public types as string
     * @return String
     */
    public function getPublicTypesListString()
    {
        if (empty($this->publicTypes)) return "";
        $ptNames = array();
        foreach ($this->publicTypes as $pt) {
            $ptNames[] = $pt->getName();
        }

        return implode (", ",$ptNames);
    }
}
