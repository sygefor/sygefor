<?php

/**
 * Auteur: Blaise de Carné - blaise@concretis.com.
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Term\PublicType;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class ProfessionalSituationTraitInstitution.
 */
trait ProfessionalSituationTrait
{
    /**
     * @var PublicType
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Term\PublicType")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "inscription", "api.profile"})
     */
    protected $publicType;

    /**
     * @var string
     * @ORM\Column(name="otherPublicType", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "inscription", "api.profile"})
     */
    protected $otherPublicType;

    /**
     * @var string
     * @ORM\Column(name="function", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "inscription", "api.profile"})
     */
    protected $function;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=512, nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "inscription", "api.profile"})
     */
    protected $status;

    /**
     * @ORM\Column(name="is_paying", type="boolean")
     * @Serializer\Groups({"trainee", "inscription", "api.profile","api.token"})
     */
    protected $isPaying = false;

    /**
     * Copy professional situation informations from another entity.
     *
     * @param ProfessionalSituationTrait $entity
     * @param bool                       $force
     */
    public function copyProfessionalSituation($entity, $force = true)
    {
        $propertyAccessor = new PropertyAccessor();
        $properties = array(
            'publicType',
            'otherPublicType',
            'function',
            'status',
            'isPaying',
        );

        foreach ($properties as $property) {
            $thisValue = $propertyAccessor->getValue($this, $property);
            if ($force || !$thisValue) {
                $propertyAccessor->setValue($this, $property, $propertyAccessor->getValue($entity, $property));
            }
        }
    }

    /**
     * @param PublicType
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
     * @return string
     */
    public function getOtherPublicType()
    {
        return $this->otherPublicType;
    }

    /**
     * @param string $otherPublicType
     */
    public function setOtherPublicType($otherPublicType)
    {
        $this->otherPublicType = $otherPublicType;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param string $function
     */
    public function setFunction($function)
    {
        $this->function = $function;
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

    /**
     * @return bool
     */
    public function getIsPaying()
    {
        return $this->isPaying;
    }

    /**
     * @param bool $isPaying
     */
    public function setIsPaying($isPaying)
    {
        $this->isPaying = $isPaying;
    }
}
