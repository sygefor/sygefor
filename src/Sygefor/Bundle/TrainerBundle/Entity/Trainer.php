<?php
namespace Sygefor\Bundle\TrainerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\CoordinatesTrait;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Trainer
 *
 * @ORM\Table(name="trainer")
 * @ORM\Entity
 * @UniqueEntity(fields={"email", "organization"}, message="Cette adresse email est déjà utilisée.", ignoreNull=true, groups={"Default", "trainer"})
 */
class Trainer implements SerializedAccessRights
{
    use ORMBehaviors\Timestampable\Timestampable;
    use PersonTrait;
    use CoordinatesTrait;

    /**
     * @var integer id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "trainer", "session"})
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session", mappedBy="trainers")
     * @Serializer\Groups({"trainer"})
     */
    protected $sessions;

    /**
     * @var boolean $isUrfist
     * @ORM\Column(name="is_urfist", type="boolean", nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $isUrfist;

    /**
     * @var $organization
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $organization;

    /**
     * @var boolean $isPublic
     * @ORM\Column(name="is_public", type="boolean")
     * @Serializer\Groups({"trainer"})
     */
    protected $isPublic;

    /**
     * @var string Institution
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\Institution")
     * @Serializer\Groups({"trainer"})
     */
    protected $institution;

    /**
     * @var string otherInstitution
     * @ORM\Column(name="other_institution", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $otherInstitution;

    /**
     * @var string $service
     * @ORM\Column(name="service", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $service;

    /**
     * @var string $status
     * @ORM\Column(name="status", type="string", length=512, nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $status;

    /**
     * @var string $responsabilities
     * @ORM\Column(name="responsabilities", type="text", nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $responsabilities;

    /**
     * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\TrainerBundle\Entity\Term\CompetenceField", cascade={"persist"})
     * @ORM\JoinTable(name="trainer__trainer_competence_fields",
     *      joinColumns={@ORM\JoinColumn(name="trainer_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="competence_field_id", referencedColumnName="id")}
     * )
     * @Serializer\Groups({"trainer"})
     */
    protected $competenceFields;

    /**
     * @var string $observations
     * @ORM\Column(name="observations", type="text", nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $observations;

    /**
     *
     */
    function __construct()
    {
        $this->competenceFields = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->addressType = 0;
        $this->isPublic = false;
    }

    /**
     * @param mixed $competenceFields
     */
    public function setCompetenceFields($competenceFields)
    {
        $this->competenceFields = $competenceFields;
    }

	/**
	 * @return mixed
	 */
	public function getCompetenceFields()
	{
		return $this->competenceFields;
	}

	/**
	 * @param $competenceFields
	 */
    public function addCompetenceFields($competenceFields)
    {
        $this->competenceFields->add($competenceFields);
    }

	/**
	 * @param $competenceFields
	 */
	public function removeCompetenceFields($competenceFields)
	{
		$this->competenceFields->remove($competenceFields);
	}

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return string
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param string $otherInstitution
     */
    public function setOtherInstitution($otherInstitution)
    {
        $this->otherInstitution = $otherInstitution;
    }

    /**
     * @return string
     */
    public function getOtherInstitution()
    {
        return $this->otherInstitution;
    }

    /**
     * @param boolean $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * @return boolean
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * @param boolean $isUrfist
     */
    public function setIsUrfist($isUrfist)
    {
        $this->isUrfist = $isUrfist;
    }

    /**
     * @return boolean
     */
    public function getIsUrfist()
    {
        return $this->isUrfist;
    }

    /**
     * @param string $observations
     */
    public function setObservations($observations)
    {
        $this->observations = $observations;
    }

    /**
     * @return string
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param string $responsabilities
     */
    public function setResponsabilities($responsabilities)
    {
        $this->responsabilities = $responsabilities;
    }

    /**
     * @return string
     */
    public function getResponsabilities()
    {
        return $this->responsabilities;
    }

    /**
     * @param string $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $sessions
     */
    public function setSessions($sessions)
    {
        $this->sessions = $sessions;
    }

    /**
     * @return mixed
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }


}
