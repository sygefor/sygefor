<?php

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\MyCompanyBundle\Form\LongTrainingType;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;

/**
 * Formation longue.
 *
 * @ORM\Entity
 * @ORM\Table(name="long_training")
 */
class LongTraining extends AbstractTraining
{
    /**
     * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType")
     * @ORM\JoinTable(name="longtraining__longtraining_public_type",
     *      joinColumns={@ORM\JoinColumn(name="longTraining_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="public_type_id", referencedColumnName="id")}
     * )
     * @Serializer\Groups({"training", "inscription", "api"})
     */
    protected $publicTypes;

    /**
     * @ORM\Column(name="prerequisites", type="text", nullable=true)
     *
     * @var string
     * @Serializer\Groups({"training", "api"})
     */
    protected $prerequisites;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\MyCompanyBundle\Entity\Module", mappedBy="training", cascade={"remove", "persist"})
     * @Serializer\Groups({"training", "session", "api", "api.attendance"})
     */
    protected $modules;

    public function __construct()
    {
        $this->modules = new ArrayCollection();
        $this->publicTypes = new ArrayCollection();

        parent::__construct();
    }

    public function __clone()
    {
        $this->modules = new ArrayCollection();
        $this->publicTypes = new ArrayCollection();

        parent::__clone();
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
        if (!$this->publicTypes->contains($publicType)) {
            $this->publicTypes->add($publicType);
        }
    }

    /**
     * @param $publicType
     */
    public function removePublicType($publicType)
    {
        if ($this->publicTypes->contains($publicType)) {
            $this->publicTypes->removeElement($publicType);
        }
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

    /**
     * @return string
     */
    public function getPrerequisites()
    {
        return $this->prerequisites;
    }

    /**
     * @param string $prerequisites
     */
    public function setPrerequisites($prerequisites)
    {
        $this->prerequisites = $prerequisites;
    }

    /**
     * @param ArrayCollection $modules
     */
    public function setModules($modules)
    {
        $this->modules = $modules;
    }

    /**
     * @param Module $module
     *
     * @return bool
     */
    public function addModule($module)
    {
        if ( ! $this->modules->contains($module)) {
            $this->modules->add($module);

            return true;
        }

        return false;
    }

    /**
     * @return ArrayCollection
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * @param Module $module
     *
     * @return bool
     */
    public function removeModule($module)
    {
        if ($this->modules->contains($module)) {
            $this->modules->remove($module);

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    static public function getType()
    {
        return 'long_training';
    }

    /**
     * @return string
     */
    static public function getTypeLabel()
    {
        return 'Formation longue';
    }

    /**
     * @return string
     */
    static public function getFormType()
    {
        return LongTrainingType::class;
    }
}
