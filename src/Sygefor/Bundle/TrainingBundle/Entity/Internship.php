<?php

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublicType;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TrainingBundle\Form\InternshipType;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="internship")
 * traduction: stage
 */
class Internship extends Training
{
    /**
     * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Term\PublicType")
     * @ORM\JoinTable(name="internship__internship_public_type",
     *      joinColumns={@ORM\JoinColumn(name="intership_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="public_type_id", referencedColumnName="id")}
     * )
     * @Serializer\Groups({"training", "inscription", "api"})
     */
    protected $publicTypes;

    /**
     * @ORM\Column(name="prerequisite", type="text", nullable=true)
     * @var String
     * @Serializer\Groups({"training", "api"})
     */
    protected $prerequisite;


    public function __construct()
    {
        $this->publicTypes = new ArrayCollection();
    }

    /**
     * @return string
     */
    static public function getType()
    {
        return 'internship';
    }

    /**
     * @return string
     */
    static public function getTypeLabel()
    {
        return 'Stage';
    }

    /**
     * @return string
     */
    static public function getFormType()
    {
        return 'internshiptype';
    }

    /**
     * @param String $prerequisite
     */
    public function setPrerequisite($prerequisite)
    {
        $this->prerequisite = $prerequisite;
    }

    /**
     * @return String
     */
    public function getPrerequisite()
    {
        return $this->prerequisite;
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
     * @param $public
     */
    public function addPublicType($publicType)
    {
        $this->publicTypes->add($publicType);
    }

    /**
     * @param $public
     */
    public function removePublicType($publicType)
    {
         $this->publicTypes->remove($publicType);
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
