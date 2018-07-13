<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 7/26/17
 * Time: 4:47 PM.
 */

namespace AppBundle\Entity\Training;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use AppBundle\Form\Type\Training\InternshipType;
use Doctrine\Common\Collections\ArrayCollection;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;

/**
 * Stage.
 *
 * @ORM\Entity
 * @ORM\Table(name="internship")
 */
class Internship extends AbstractTraining
{
    use TrainingTrait;

    /**
     * @var string
     * @ORM\Column(name="public_types", type="text", nullable=true)
     * @Serializer\Groups({"training", "inscription", "api"})
     */
    protected $publicTypes;

    /**
     * @var string
     * @ORM\Column(name="prerequisites", type="text", nullable=true)
     * @Serializer\Groups({"training", "api"})
     */
    protected $prerequisites;

    public function __construct()
    {
        $this->tags = new ArrayCollection();

        parent::__construct();
    }

    public function __clone()
    {
        $this->number = null;
        $this->tags = new ArrayCollection();

        parent::__clone();
    }

    /**
     * @return string
     */
    public function getPublicTypes()
    {
        return $this->publicTypes;
    }

    /**
     * @param string $publicTypes
     */
    public function setPublicTypes($publicTypes)
    {
        $this->publicTypes = $publicTypes;
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
     * @return string
     */
    public static function getType()
    {
        return 'internship';
    }

    /**
     * @return string
     */
    public static function getTypeLabel()
    {
        return 'Stage';
    }

    /**
     * @return string
     */
    public static function getFormType()
    {
        return InternshipType::class;
    }
}
