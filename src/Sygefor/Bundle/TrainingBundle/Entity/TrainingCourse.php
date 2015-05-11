<?php

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\DisciplinaryTrait;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Institution;
use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\TrainingBundle\Form\TrainingCourseType;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="training_course")
 * traduction: enseignement de cursus
 *
 */
class TrainingCourse extends Training
{
    use DisciplinaryTrait;

    /**
     * @var Institution
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\Institution")
     * @Serializer\Groups({"training", "api"})
     */
    protected $institution;

    /**
     * @ORM\Column(name="context", type="text", nullable=true)
     * @var String
     * @Serializer\Groups({"training", "api"})
     */
    protected $context;

    /**
     * @var TeachingCursus
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\TeachingCursus")
     * @Serializer\Groups({"training", "api"})
     */
    protected $teachingCursus;

    /**
     * @ORM\Column(name="otherCursus", type="string", length=255, nullable=true)
     * @var String
     * @Serializer\Groups({"training", "api"})
     */
    protected $otherCursus;

    /**
     * @ORM\Column(name="evaluation", type="boolean")
     * @var boolean
     * @Serializer\Groups({"training", "api"})
     */
    protected $evaluation;

    /**
     * @return string
     */
    static public function getType()
    {
        return 'training_course';
    }

    /**
     * @return string
     */
    static public function getTypeLabel()
    {
        return 'Enseignement de cursus';
    }

    /**
     * @return string
     */
    static public function getFormType()
    {
        return 'trainingcoursetype';
    }

    /**
     * @param String $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return String
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param boolean $evaluation
     */
    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * @return boolean
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    /**
     * @param \Sygefor\Bundle\TrainingBundle\Entity\Term\Institution $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return \Sygefor\Bundle\TrainingBundle\Entity\Term\Institution
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param String $otherCursus
     */
    public function setOtherCursus($otherCursus)
    {
        $this->otherCursus = $otherCursus;
    }

    /**
     * @return String
     */
    public function getOtherCursus()
    {
        return $this->otherCursus;
    }

    /**
     * @param \Sygefor\Bundle\TrainingBundle\Entity\Term\TeachingCursus $teachingCursus
     */
    public function setTeachingCursus($teachingCursus)
    {
        $this->teachingCursus = $teachingCursus;
    }

    /**
     * @return \Sygefor\Bundle\TrainingBundle\Entity\Term\TeachingCursus
     */
    public function getTeachingCursus()
    {
        return $this->teachingCursus;
    }
}
