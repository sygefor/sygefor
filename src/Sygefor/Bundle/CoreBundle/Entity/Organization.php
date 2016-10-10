<?php

namespace Sygefor\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait\CoordinatesTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Organization.
 *
 * IMPORTANT : serialization is handle by YML
 * to prevent rules from CoordinatesTrait being applied to private infos (trainee, trainer)
 *
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
     * @var int
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
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\CoreBundle\Entity\User\User", mappedBy="organization", cascade={"persist", "merge"})
     */
    private $users;

    /**
     * @var AbstractInstitution
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $institution;

    /**
     * @ORM\Column(name="map", type="json_array", nullable=true)
     */
    protected $map;

    /**
     * @var bool
     * @ORM\Column(name="trainee_registrable", type="boolean")
     */
    protected $traineeRegistrable = true;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->users       = new ArrayCollection();
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
     * @return bool
     */
    public function getTraineeRegistrable()
    {
        return $this->traineeRegistrable;
    }

    /**
     * @param bool $traineeRegistrable
     */
    public function setTraineeRegistrable($traineeRegistrable)
    {
        $this->traineeRegistrable = $traineeRegistrable;
    }

    function __toString()
    {
        return $this->name;
    }

    /**
     * loadValidatorMetadata.
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // CoordinateTrait
        $metadata->addPropertyConstraint('address', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner une adresse.',
        )));
        $metadata->addPropertyConstraint('zip', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un code postal.',
        )));
        $metadata->addPropertyConstraint('city', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner une ville.',
        )));
        $metadata->addPropertyConstraint('email', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un email.',
        )));
    }
}
