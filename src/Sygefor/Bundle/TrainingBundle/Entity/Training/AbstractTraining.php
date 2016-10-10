<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Training;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Sygefor\Bundle\TrainingBundle\Entity\Material\Material;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Supervisor;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Tag;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\TrainingCategory;
use Sygefor\Bundle\CoreBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="training", uniqueConstraints={@ORM\UniqueConstraint(name="organization_number", columns={"number", "organization_id"})})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({})
 * Traduction: Formation
 */
abstract class AbstractTraining implements SerializedAccessRights
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    private $id;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization", inversedBy="trainings")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     * @Serializer\Groups({"Default", "training", "api"})
     */
    protected $organization;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession", mappedBy="training", cascade={"persist", "remove"})
     * @Serializer\Groups({"training", "api.training"})
     */
    protected $sessions;

    /**
     * @ORM\Column(name="number", type="integer")
     * @Serializer\Groups({"Default", "api"})
     */
    private $number;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="Vous devez renseigner un intitulé.")
     *
     * @var string
     * @Serializer\Groups({"Default", "api"})
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Theme")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Vous devez renseigner une thématique.")
     * @Serializer\Groups({"training", "session", "inscription", "api"})
     */
    protected $theme;

    /**
     * @ORM\Column(name="program", type="text", nullable=true)
     *
     * @var string
     * @Serializer\Groups({"training", "api"})
     */
    protected $program;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @var string
     * @Serializer\Groups({"training", "api"})
     */
    protected $description;

    /**
     * @ORM\Column(name="teaching_methods", type="text", nullable=true)
     *
     * @var string
     * @Serializer\Groups({"training", "api"})
     */
    protected $teachingMethods;

    /**
     * @var AbstractInstitution Institution
     *
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Serializer\Groups({"training", "api"})
     */
    protected $institution;

    /**
     * @var Supervisor
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Supervisor")
     * @Serializer\Groups({"training", "api.training", "session"})
     */
    protected $supervisor;

    /**
     * @var TrainingCategory
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Training\Term\TrainingCategory")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"training", "api"})
     */
    protected $category;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Tag")
     * @ORM\JoinTable(name="training__training_tag",
     *      joinColumns={@ORM\JoinColumn(name="training_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="cascade")}
     * )
     * @Serializer\Groups({"training", "api"})
     */
    protected $tags;

    /**
     * @ORM\Column(name="interventionType", type="string", length=255, nullable=true)
     *
     * @var string
     * @Serializer\Groups({"training", "api"})
     */
    protected $interventionType;

    /**
     * @ORM\Column(name="externalInitiative", type="boolean", nullable=true)
     *
     * @var bool
     * @Serializer\Groups({"training"})
     */
    protected $externalInitiative;

    /**
     * @ORM\Column(name="comments", type="text", nullable=true)
     *
     * @var string
     * @Serializer\Groups({"training"})
     */
    protected $comments;

    /**
     * @ORM\Column(name="firstSessionPeriodSemester", type="integer")
     * @Assert\NotNull
     *
     * @var int
     * @Serializer\Groups({"training", "api"})
     */
    protected $firstSessionPeriodSemester = 1;

    /**
     * @ORM\Column(name="firstSessionPeriodYear", type="integer")
     * @Assert\NotNull
     *
     * @var int
     * @Serializer\Groups({"training", "api"})
     */
    protected $firstSessionPeriodYear;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Material\Material", mappedBy="training", cascade={"remove", "persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"training", "session", "api.attendance"})
     */
    protected $materials;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sessions  = new ArrayCollection();
        $this->materials = new ArrayCollection();
        $this->tags     = new ArrayCollection();
    }

    /**
     * cloning magic function.
     */
    public function __clone()
    {
        $this->id     = null;
        $this->number = null;
        $this->setCreatedAt(new \DateTime());

        //sessions are not copied.
        $this->materials = new ArrayCollection();
        $this->sessions  = new ArrayCollection();
        $this->tags      = new ArrayCollection();
    }

    /**
     * @return string
     *                Serializer : via listener to include in all cases
     */
    static function getType()
    {
        return 'training';
    }

    /**
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "api"})
     */
    static function getTypeLabel()
    {
        return 'Formation';
    }

    /**
     * @return mixed
     */
    static public function getFormType()
    {
        return 'Sygefor\Bundle\TrainingBundle\Form\TrainingType';
    }

    /**
     * @param $addMethod
     * @param ArrayCollection $arrayCollection
     */
    public function duplicateArrayCollection($addMethod, $arrayCollection)
    {
        foreach ($arrayCollection as $item) {
            if (method_exists($this, $addMethod)) {
                $this->$addMethod($item);
            }
        }
    }

    /**
     * Copy all properties from a training except id and number.
     *
     * @param Training $originalTraining
     */
    public function copyProperties($originalTraining)
    {
        foreach (array_keys(get_object_vars($this)) as $key) {
            if ($key !== 'id' && $key !== 'number' && $key !== 'sessions' && $key !== 'session') {
                if (isset($originalTraining->$key)) {
                    $this->$key = $originalTraining->$key;
                }
            }
        }
    }

    /**
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "session", "api"})
     */
    public function getSerial()
    {
        if($this->getOrganization()) {
            $parts = array(
                $this->getOrganization()->getCode(),
                $this->getType(),
                $this->getNumber(),
            );

            return implode('-', $parts);
        }

        return;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param ArrayCollection $sessions
     */
    public function setSessions($sessions)
    {
        $this->sessions = $sessions;
    }

    /**
     * @param AbstractSession $session
     */
    public function addSession($session)
    {
        $this->sessions->add($session);
    }

    /**
     * @param AbstractSession $session
     */
    public function removeSession($session)
    {
        $this->sessions->removeElement($session);
    }

    /**
     * @return ArrayCollection
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @return mixed
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param mixed $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return string
     */
    public function getProgram()
    {
        return $this->program;
    }

    /**
     * @param string $program
     */
    public function setProgram($program)
    {
        $this->program = $program;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getTeachingMethods()
    {
        return $this->teachingMethods;
    }

    /**
     * @param string $teachingMethods
     */
    public function setTeachingMethods($teachingMethods)
    {
        $this->teachingMethods = $teachingMethods;
    }

    /**
     * @return AbstractInstitution
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param AbstractInstitution $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return Supervisor
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * @param Supervisor $supervisor
     */
    public function setSupervisor($supervisor)
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @return TrainingCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param TrainingCategory $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param ArrayCollection $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param Tag $tag
     *
     * @return bool
     */
    public function addTag($tag)
    {
        if ( ! $this->tags->contains($tag)) {
            $this->tags->add($tag);

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getInterventionType()
    {
        return $this->interventionType;
    }

    /**
     * @param string $interventionType
     */
    public function setInterventionType($interventionType)
    {
        $this->interventionType = $interventionType;
    }

    /**
     * @return boolean
     */
    public function isExternalInitiative()
    {
        return $this->externalInitiative;
    }

    /**
     * @param boolean $externalInitiative
     */
    public function setExternalInitiative($externalInitiative)
    {
        $this->externalInitiative = $externalInitiative;
    }

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return int
     */
    public function getFirstSessionPeriodSemester()
    {
        return $this->firstSessionPeriodSemester;
    }

    /**
     * @param int $firstSessionPeriodSemester
     */
    public function setFirstSessionPeriodSemester($firstSessionPeriodSemester)
    {
        $this->firstSessionPeriodSemester = $firstSessionPeriodSemester;
    }

    /**
     * @return int
     */
    public function getFirstSessionPeriodYear()
    {
        return $this->firstSessionPeriodYear;
    }

    /**
     * @param int $firstSessionPeriodYear
     */
    public function setFirstSessionPeriodYear($firstSessionPeriodYear)
    {
        $this->firstSessionPeriodYear = $firstSessionPeriodYear;
    }

    /**
     * @param ArrayCollection $materials
     */
    public function setMaterials($materials)
    {
        $this->materials = $materials;
    }

    /**
     * @param Material $material
     */
    public function addMaterial($material)
    {
        $material->setTraining($this);
        $this->materials->add($material);
    }

    /**
     * @return ArrayCollection
     */
    public function getMaterials()
    {
        return $this->materials;
    }

    /**
     * Used for duplicate training choose type form.
     *
     * @return string
     */
    public function getDuplicatedType()
    {
        return $this->getType();
    }

    /**
     * Used for duplicate training choose type form.
     */
    public function setDuplicatedType($type) {}

    /**
     * @return string
     */
    function __toString()
    {
        return $this->getName();
    }
}
