<?php

namespace Sygefor\Bundle\InstitutionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait\CoordinatesTrait;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\InstitutionBundle\Entity\Term\GeographicOrigin;
use Sygefor\Bundle\InstitutionBundle\Entity\Term\InstitutionType;
use Sygefor\Bundle\CoreBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Sygefor\Bundle\InstitutionBundle\Form\BaseInstitutionType as FormType;

/**
 * Institution.
 *
 * @ORM\Table(name="institution")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
abstract class AbstractInstitution implements SerializedAccessRights
{
    use ORMBehaviors\Timestampable\Timestampable;
    use CoordinatesTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @var Organization Organization
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization")
     * @Assert\NotNull(message="Vous devez renseigner un centre de rattachement.")
     * @Serializer\Groups({"Default", "api", "api.institution"})})
     */
    protected $organization;

    /**
     * @var string name
     * @ORM\Column(name="name", type="string", length=512)
     * @Assert\NotBlank(message="Vous devez renseigner un nom d'établissement.")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\InstitutionBundle\Entity\Term\InstitutionType")
     * @ORM\JoinColumn(name="institutionType_id", nullable=true)
     * @Serializer\Groups({"institution", "trainee", "api.institution"})
     */
    protected $institutionType;

    /**
     * @ORM\OneToOne(targetEntity="AbstractCorrespondent", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="manager_id", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     * @Serializer\Groups({"institution", "trainee", "api.institution"})
     */
    protected $manager;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\InstitutionBundle\Entity\Term\GeographicOrigin")
     * @ORM\JoinColumn(name="geographic_origin_id", nullable=true)
     * @Serializer\Groups({"institution", "trainee", "trainee"})
     */
    protected $geographicOrigin;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return Organization Organization
     */
    public function getOrganization()
    {
        return $this->organization;
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
     * @return InstitutionType
     */
    public function getInstitutionType()
    {
        return $this->institutionType;
    }

    /**
     * @param InstitutionType $institutionType
     */
    public function setInstitutionType($institutionType)
    {
        $this->institutionType = $institutionType;
    }

    /**
     * @return mixed
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param mixed $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return GeographicOrigin
     */
    public function getGeographicOrigin()
    {
        return $this->geographicOrigin;
    }

    /**
     * @param GeographicOrigin $geographicOrigin
     */
    public function setGeographicOrigin($geographicOrigin)
    {
        $this->geographicOrigin = $geographicOrigin;
    }

    function __toString()
    {
        return $this->getName();
    }

    public static function getFormType()
    {
        return FormType::class;
    }

    public static function getType()
    {
        return 'institution';
    }
}
