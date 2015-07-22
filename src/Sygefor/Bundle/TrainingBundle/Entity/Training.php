<?php

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Tag;
use Sygefor\Bundle\TrainingBundle\Form\TrainingType;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="training", uniqueConstraints={@ORM\UniqueConstraint(name="organization_number", columns={"number", "organization_id"})})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({})
 * traduction: formation
 */
abstract class Training implements SerializedAccessRights
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="number", type="integer")
     * @Serializer\Groups({"Default", "api"})
     */
    private $number;

    /**
     * @var ArrayCollection $sessions
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session", mappedBy="training", cascade={"persist", "remove"})
     * @Serializer\Groups({"training", "api.training"})
     */
    protected $sessions;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization", inversedBy="trainings")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     * @Serializer\Groups({"Default", "training", "api"})
     */
    protected $organization;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\Theme")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Vous devez renseigner une thématique.")
     * @Serializer\Groups({"training", "session", "inscription", "api"})
     */
    protected $theme;

    /**
     * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\Tag")
     * @ORM\JoinTable(name="training__training_tag",
     *      joinColumns={@ORM\JoinColumn(name="training_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     * )
     * @Serializer\Groups({"training", "api"})
     */
    protected $tags;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="Vous devez renseigner un intitulé.")
     * @var string
     * @Serializer\Groups({"Default", "api"})
     */
    protected $name;

    /**
     * @ORM\Column(name="objectives", type="text", nullable=true)
     * @var String
     * @Serializer\Groups({"training", "api"})
     */
    protected $objectives;

    /**
     * @ORM\Column(name="program", type="text", nullable=true)
     * @var String
     * @Serializer\Groups({"training", "api"})
     */
    protected $program;

    /**
     * @ORM\Column(name="firstSessionPeriodSemester", type="integer")
     * @Assert\NotNull
     * @var integer
     * @Serializer\Groups({"training", "api"})
     */
    protected $firstSessionPeriodSemester = 1;

    /**
     * @ORM\Column(name="firstSessionPeriodYear", type="integer")
     * @Assert\NotNull
     * @var integer
     * @Serializer\Groups({"training", "api"})
     */
    protected $firstSessionPeriodYear;

    /**
     * @ORM\Column(name="interventionType", type="string", length=255, nullable=true)
     * @var String
     * @Serializer\Groups({"training", "api"})
     */
    protected $interventionType;

    /**
     * @ORM\Column(name="externInitiative", type="boolean", nullable=true)
     * @var boolean
     * @Serializer\Groups({"training"})
     */
    protected $externInitiative;

    /**
     * @ORM\Column(name="supervisor", type="string", length=255, nullable=true)
     * @var String
     * @Serializer\Groups({"training"})
     */
    protected $supervisor;

    /**
     * @ORM\Column(name="resources", type="text", nullable=true)
     * @var String
     * @Serializer\Groups({"training"})
     */
    protected $resources;

    /**
     * @ORM\Column(name="comments", type="text", nullable=true)
     * @var String
     * @Serializer\Groups({"training"})
     */
    protected $comments;

    /**
     * @var ArrayCollection $materials
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Material", mappedBy="training", cascade={"remove", "persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"training", "api.attendance"})
     */
    protected $materials;

    /**
     * @return string
     * Serializer : via listener to include in all cases
     */
    static function getType()
    {
        return "training";
    }

    /**
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "api"})
     */
    static function getTypeLabel()
    {
        return "Formation";
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->sessions = new ArrayCollection();
    }

    /**
     * cloning magic function
     */
    public function __clone()
    {
        $this->id = null ;
        $this->number = null ;
        $this->setCreatedAt(new \DateTime());
        //sessions are not copied.
        $this->sessions = new ArrayCollection();
        $this->materials = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @param $addMethod
     * @param ArrayCollection $arrayCollection
     */
    public function duplicateArrayCollection($addMethod, $arrayCollection)
    {
        foreach ($arrayCollection as $item) {
            $this->$addMethod($item);
        }
    }

    /**
     * @param int $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param String $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return String
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param boolean $externInitiative
     */
    public function setExternInitiative($externInitiative)
    {
        $this->externInitiative = $externInitiative;
    }

    /**
     * @return boolean
     */
    public function getExternInitiative()
    {
        return $this->externInitiative;
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
     * @param String $interventionType
     */
    public function setInterventionType($interventionType)
    {
        $this->interventionType = $interventionType;
    }

    /**
     * @return String
     */
    public function getInterventionType()
    {
        return $this->interventionType;
    }

    /**
     * @param String $objectives
     */
    public function setObjectives($objectives)
    {
        $this->objectives = $objectives;
    }

    /**
     * @return String
     */
    public function getObjectives()
    {
        return $this->objectives;
    }

    /**
     * @param Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param String $program
     */
    public function setProgram($program)
    {
        $this->program = $program;
    }

    /**
     * @return String
     */
    public function getProgram()
    {
        return $this->program;
    }

    /**
     * @param int $firstSessionPeriodSemester
     */
    public function setFirstSessionPeriodSemester($firstSessionPeriodSemester)
    {
        $this->firstSessionPeriodSemester = $firstSessionPeriodSemester;
    }

    /**
     * @return boolean
     */
    public function getFirstSessionPeriodSemester()
    {
        return $this->firstSessionPeriodSemester;
    }

    /**
     * @param String $firstSessionPeriodYear
     */
    public function setFirstSessionPeriodYear($firstSessionPeriodYear)
    {
        $this->firstSessionPeriodYear = $firstSessionPeriodYear;
    }

    /**
     * @return String
     */
    public function getFirstSessionPeriodYear()
    {
        return $this->firstSessionPeriodYear;
    }

    /**
     * @return String
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "session", "api"})
     */
    public function getSerial()
    {
        if($this->getOrganization()) {
            $parts = [
                $this->getOrganization()->getCode(),
                $this->getType(),
                $this->getNumber()
            ];
            return join("-", $parts);
        }
        return null;
    }

    /**
     * @param String $supervisor
     */
    public function setSupervisor($supervisor)
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @return String
     */
    public function getSupervisor()
    {
        return $this->supervisor;
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
     */
    public function addTag(Tag $tag)
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        if ($this->tags->contains(($tag))) {
            $this->tags->remove($tag);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return mixed
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param String $resources
     */
    public function setResources($resources)
    {
        $this->resources = $resources;
    }

    /**
     * @return String
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @return TrainingType
     */
    static public function getFormType() {
        return 'trainingtype';
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $session
     */
    public function setSessions($sessions)
    {
        $this->sessions = $sessions;
    }

    /**
     * @param $session
     */
    public function addSession($session)
    {
        $this->sessions->add($session);
    }

    /**
     * @param $session
     */
    public function removeSession($session)
    {
        $this->sessions->remove($session);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $materials
     */
    public function setMaterials($materials)
    {
        $this->materials = $materials;
    }


    /**
     * @param $material
     */
    public function addMaterial($material) {
        $material->setTraining($this);
        $this->materials->add($material);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMaterials()
    {
        return $this->materials;
    }

    /**
     * HumanReadablePropertyAccessor helper : provides a list of tags as string
     * @return String
     */
    public function getTagsListString()
    {
        if (empty($this->tags)) return "";
        $tagNames = array();
        foreach ($this->tags as $tag) {
            $tagNames[] = $tag->getName();
        }

        return implode (", ",$tagNames);
    }

    /**
     * For activity report
     *
     * Return true if the training is practical
     *
     * @return string
     */
    public function isTp()
    {
        return !!preg_match("/.*TP*.|.*travaux pratique.*/si", $this->getInterventionType());
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getName();
    }

}
