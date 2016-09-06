<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 16/02/2015
 * Time: 16:34
 */

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\DisciplinaryTrait;
use Sygefor\Bundle\TrainingBundle\Entity\Term\ApplicantOrganism;
use Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralPartner;
use Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralSchool;
use Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralYear;
use Sygefor\Bundle\TrainingBundle\Entity\Term\PedagogicPartner;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Institution;
use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\TrainingBundle\Form\TrainingCourseType;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="doctoral_training")
 * traduction: formation doctorale
 *
 */
class DoctoralTraining extends Training
{
    use DisciplinaryTrait;

    /**
     * @var ArrayCollection $institutions
     * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\Institution")
     * @ORM\JoinTable(name="doctoral_training__doctoral_training_institution",
     *      joinColumns={@ORM\JoinColumn(name="training_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id", onDelete="cascade")}
     * )
     * @Serializer\Groups({"training", "session", "api.training"})
     */
    protected $institutions;

	/**
	 * @var ArrayCollection $doctoralSchools
	 * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralSchool")
     * @ORM\JoinTable(name="doctoral_training__doctoral_training_doctoral_school",
     *      joinColumns={@ORM\JoinColumn(name="training_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="doctoralSchool_id", referencedColumnName="id", onDelete="cascade")}
     * )
	 * @Serializer\Groups({"training", "api.training"})
	 */
	protected $doctoralSchools;

	/**
	 * @var ArrayCollection $doctoralYears
	 * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralYear")
     * @ORM\JoinTable(name="doctoraltraining__doctoraltraining_doctoralyear",
     *      joinColumns={@ORM\JoinColumn(name="training_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="doctoralYear_id", referencedColumnName="id", onDelete="cascade")}
     * )
	 * @Serializer\Groups({"training", "api"})
	 */
	protected $doctoralYears;

	/**
     * @ORM\Column(name="evaluation", type="boolean", nullable=true)
     * @var boolean
     * @Serializer\Groups({"training", "api.training"})
     */
    protected $evaluation;

	/**
	 * @var ApplicantOrganism $applicantOrganism
	 * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\ApplicantOrganism", cascade={"persist"})
	 * @Serializer\Groups({"training", "session", "api.training"})
	 */
	protected $applicantOrganism;

	/**
	 * @var DoctoralPartner $doctoralPartner
	 * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralPartner", cascade={"persist"})
	 * @Serializer\Groups({"training", "session", "api.training"})
	 */
	protected $doctoralPartner;

	/**
	 * @var PedagogicPartner
	 * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\PedagogicPartner", cascade={"persist"})
	 * @Serializer\Groups({"training", "session", "api.training"})
	 */
	protected $pedagogicPartner;

	/**
	 * @var DoctoralPartner
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralPartner", cascade={"persist"})
	 * @Serializer\Groups({"training", "api.training"})
	 */
	protected $otherPartner;

    /**
     * @ORM\Column(name="ects", type="integer", nullable=true)
     * @var int
     * @Serializer\Groups({"training", "api.training"})
     */
    protected $ects;

    public function __construct()
    {
        parent::__construct();

        $this->institutions = new ArrayCollection();
        $this->doctoralYears = new ArrayCollection();
        $this->doctoralSchools = new ArrayCollection();
    }

    function __clone()
    {
        parent::__clone();

        $this->institutions = new ArrayCollection();
        $this->doctoralYears = new ArrayCollection();
        $this->doctoralSchools = new ArrayCollection();
    }

    /**
     * @return string
     */
    static public function getType()
    {
        return 'doctoral_training';
    }

    /**
     * @return string
     */
    static public function getTypeLabel()
    {
        return 'Formation doctorale';
    }

    /**
     * @return string
     */
    static public function getFormType()
    {
        return 'doctoraltrainingtype';
    }

    /**
     * Return college year for semestered doctoral training elastica filter
     * @return string
     */
    public function getCollegeYear()
    {
        if ($this->getFirstSessionPeriodSemester() < 2) {
            return strval($this->getFirstSessionPeriodYear() - 1) . "-" . strval($this->getFirstSessionPeriodYear());
        }
        else {
            return strval($this->getFirstSessionPeriodYear()) . "-" . strval($this->getFirstSessionPeriodYear() + 1);
        }
    }

    /**
     * Return college semester for elastica filter
     * @return string
     */
    public function getCollegeSemester()
    {
        return $this->getFirstSessionPeriodSemester() === 1 ? 2 : 1;
    }

    /**
     * @return ArrayCollection
     */
    public function getInstitutions()
    {
        return $this->institutions;
    }

    /**
     * @param ArrayCollection $institutions
     */
    public function setInstitutions($institutions)
    {
        $this->institutions = $institutions;
    }

    /**
     * @param Institution $institution
     * @return $this
     */
    public function addInstitution($institution)
    {
        if (!$this->institutions->contains($institution)) {
            $this->institutions->add($institution);
        }

        return $this;
    }

    /**
     * @param Institution $institution
     * @return $this
     */
    public function removeInstitution($institution)
    {
        if ($this->institutions->contains($institution)) {
            $this->institutions->removeElement($institution);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDoctoralSchools()
    {
        return $this->doctoralSchools;
    }

    /**
     * @param ArrayCollection $doctoralSchools
     */
    public function setDoctoralSchools($doctoralSchools)
    {
        $this->doctoralSchools = $doctoralSchools;
    }

    /**
     * @param DoctoralSchool $doctoralSchool
     * @return $this
     */
    public function addDoctoralSchool($doctoralSchool)
    {
        if (!$this->doctoralSchools->contains($doctoralSchool)) {
            $this->doctoralSchools->add($doctoralSchool);
        }

        return $this;
    }

    /**
     * @param DoctoralSchool $doctoralSchool
     * @return $this
     */
    public function removeDoctoralSchool($doctoralSchool)
    {
        if ($this->doctoralSchools->contains($doctoralSchool)) {
            $this->doctoralSchools->removeElement($doctoralSchool);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDoctoralYears()
    {
        return $this->doctoralYears;
    }

    /**
     * @param ArrayCollection $doctoralYears
     */
    public function setDoctoralYears($doctoralYears)
    {
        $this->doctoralYears = $doctoralYears;
    }

    /**
     * @param DoctoralYear $doctoralYear
     * @return $this
     */
    public function addDoctoralYear($doctoralYear)
    {
        if (!$this->doctoralYears->contains($doctoralYear)) {
            $this->doctoralYears->add($doctoralYear);
        }

        return $this;
    }

    /**
     * @param DoctoralYear $doctoralYear
     * @return $this
     */
    public function removeDoctoralYear($doctoralYear)
    {
        if ($this->doctoralYears->contains($doctoralYear)) {
            $this->doctoralYears->removeElement($doctoralYear);
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    /**
     * @param boolean $evaluation
     */
    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * @return ApplicantOrganism
     */
    public function getApplicantOrganism()
    {
        return $this->applicantOrganism;
    }

    /**
     * @param ApplicantOrganism $applicantOrganism
     */
    public function setApplicantOrganism($applicantOrganism)
    {
        $this->applicantOrganism = $applicantOrganism;
    }

    /**
     * @return DoctoralPartner
     */
    public function getDoctoralPartner()
    {
        return $this->doctoralPartner;
    }

    /**
     * @param DoctoralPartner $doctoralPartner
     */
    public function setDoctoralPartner($doctoralPartner)
    {
        $this->doctoralPartner = $doctoralPartner;
    }

    /**
     * @return PedagogicPartner
     */
    public function getPedagogicPartner()
    {
        return $this->pedagogicPartner;
    }

    /**
     * @param PedagogicPartner $pedagogicPartner
     */
    public function setPedagogicPartner($pedagogicPartner)
    {
        $this->pedagogicPartner = $pedagogicPartner;
    }

    /**
     * @return DoctoralPartner
     */
    public function getOtherPartner()
    {
        return $this->otherPartner;
    }

    /**
     * @param DoctoralPartner $otherPartner
     */
    public function setOtherPartner($otherPartner)
    {
        $this->otherPartner = $otherPartner;
    }

    /**
     * @return int
     */
    public function getEcts()
    {
        return $this->ects;
    }

    /**
     * @param int $ects
     */
    public function setEcts($ects)
    {
        $this->ects = $ects;
    }

    /**
     * Fot activity report, geographic origin
     */
    public function getInstitution() {
        return $this->getInstitutions()->first();
    }
}