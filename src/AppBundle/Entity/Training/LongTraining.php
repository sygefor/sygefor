<?php

namespace AppBundle\Entity\Training;


use AppBundle\Entity\Term\PublicType;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Form\Type\Training\LongTrainingType;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;

/**
 * Formation longue.
 *
 * @ORM\Entity
 * @ORM\Table(name="long_training")
 */
class LongTraining extends AbstractTraining
{
    use TrainingTrait;

    /**
     * @var PublicType
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Term\PublicType")
     * @Serializer\Groups({"training", "inscription", "api"})
     */
    protected $publicType;

    /**
     * @ORM\Column(name="prerequisites", type="text", nullable=true)
     *
     * @var string
     * @Serializer\Groups({"training", "api"})
     */
    protected $prerequisites;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Training\Module", mappedBy="training", cascade={"remove", "persist"})
     * @Serializer\Groups({"training", "session", "api", "api.attendance"})
     */
    protected $modules;

    public function __construct()
    {
        $this->modules = new ArrayCollection();

        parent::__construct();
    }

    public function __clone()
    {
        $this->modules = new ArrayCollection();

        parent::__clone();
    }


    /**
     * @param mixed $publicType
     */
    public function setPublicType($publicType)
    {
        $this->publicType = $publicType;
    }

    /**
     * @return mixed
     */
    public function getPublicType()
    {
        return $this->publicType;
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
        if (!$this->modules->contains($module)) {
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
