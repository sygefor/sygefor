<?php
namespace Sygefor\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Institution;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Organization
 *
 * IMPORTANT : serialization is handle by YML
 * to prevent rules from CoordinatesTrait being applied to private infos (trainee, trainer)
 * @see Resources/config/serializer/Entity.Organization.yml
 * NO SERIALIZATION INFO IN ANNOTATIONS !!!
 *
 * @ORM\Table(name="organization")
 * @ORM\Entity
 */
class Organization
{
    use CoordinatesTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=32)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="departments", type="json_array")
     */
    protected $departments;

    /**
     * @var ArrayCollection $users
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\UserBundle\Entity\User", mappedBy="organization", cascade={"persist", "merge"})
     */
    private $users;

    /**
     * @var Institution $institution
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\Institution")
     */
    private $institution;

    /**
     * @ORM\Column(name="map", type="json_array", nullable=true)
     */
    protected $map;

    /**
     * @var bool $traineeRegistrable
     * @ORM\Column(name="trainee_registrable", type="boolean")
     */
    protected $traineeRegistrable = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->departments = new ArrayCollection();
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
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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
     * @return array
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     * @param array $departments
     */
    public function setDepartments($departments)
    {
        $this->departments = $departments;
    }

    /**
     * @param ArrayCollection $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $map
     */
    public function setMap($map)
    {
        $this->map = $map;
    }

    /**
     * @return mixed
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @return Institution
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param Institution $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return boolean
     */
    public function getTraineeRegistrable()
    {
        return $this->traineeRegistrable;
    }

    /**
     * @param boolean $traineeRegistrable
     */
    public function setTraineeRegistrable($traineeRegistrable)
    {
        $this->traineeRegistrable = $traineeRegistrable;
    }

    function __toString()
    {
        return $this->name;
    }
}
