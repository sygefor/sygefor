<?php
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 */
namespace Sygefor\Bundle\CoreBundle\Entity\PersonTrait;

use Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\ExecutionContextInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class ProfessionalSituationTraitInstitution
 * @package Sygefor\Bundle\CoreBundle\Entity
 */
trait ProfessionalSituationTrait
{
    /**
     * @var AbstractInstitution Institution
     * @Assert\NotNull(message="Vous devez renseigner un établissement ou une entreprise.", groups={"api.profile"})
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Serializer\Groups({"trainee", "trainer", "inscription", "session", "api.profile"})
     */
    protected $institution;

    /**
     * @var PublicType
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "inscription", "api.profile"})
     */
    protected $publicType;

    /**
     * @var string service
     * @ORM\Column(name="service", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "inscription", "api.profile"})
     */
    protected $service;

    /**
     * @ORM\Column(name="is_paying", type="boolean")
     * @Serializer\Groups({"trainee", "inscription", "api.profile","api.token"})
     */
    protected $isPaying = false;

    /**
     * @var string status
     * @ORM\Column(name="status", type="string", length=512, nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "inscription", "api.profile"})
     */
    protected $status;

    /**
     * Copy professional situation informations from another entity
     *
     * @param ProfessionalSituationTrait $entity
     * @param boolean $force
     */
    public function copyProfessionalSituation($entity, $force = true)
    {
        $propertyAccessor = new PropertyAccessor();
        foreach (array('institution', 'publicType', 'service', 'isPaying', 'status') as $property) {
            $thisValue = $propertyAccessor->getValue($this, $property);
            if ($force || ! $thisValue) {
                $propertyAccessor->setValue($this, $property, $propertyAccessor->getValue($entity, $property));
            }
        }
    }

    /**
     * @param AbstractInstitution $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }
    /**
     * @return AbstractInstitution
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $publicType
     */
    public function setPublicType($publicType)
    {
        $this->publicType = $publicType;
    }

    /**
     * @return PublicType
     */
    public function getPublicType()
    {
        return $this->publicType;
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
     * @return boolean
     */
    public function getIsPaying()
    {
        return $this->isPaying;
    }

    /**
     * @param boolean $isPaying
     */
    public function setIsPaying($isPaying)
    {
        $this->isPaying = $isPaying;
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
