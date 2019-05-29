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
	 * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
	 * @Serializer\Groups({"Default", "trainee","inscription", "api.profile"})
	 */
	protected $publicType;

	/**
	 * @var string
	 * @ORM\Column(name="otherPublicType", type="string", length=255, nullable=true)
	 * @Serializer\Groups({"Default", "trainee", "inscription", "api.profile"})
	 */
	protected $otherPublicType;

	/**
	 * @var PublicType
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Term\Trainee\PublicCategory")
	 * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
	 * @Serializer\Groups({"Default", "trainee", "inscription", "api.profile"})
	 */
	protected $publicCategory;

	/**
	 * @var string
	 * @ORM\Column(name="position", type="string", length=256, nullable=true)
	 * @Serializer\Groups({"Default", "trainee", "trainer", "inscription", "api.profile"})
	 */
	protected $position;

	/**
	 * @var string service
	 * @ORM\Column(name="service", type="string", length=255, nullable=true)
	 * @Serializer\Groups({"Default", "trainee", "trainer", "inscription", "api.profile"})
	 */
	protected $service;

	/**
	 * @ORM\Column(name="is_paying", type="boolean")
	 * @Serializer\Groups({"Default", "trainee", "inscription", "api.profile", "api.token"})
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
	        'service',
	        'publicType',
	        'otherPublicType',
	        'publicCategory',
	        'position',
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
	 * @return string
	 */
	public function getService()
	{
		return $this->service;
	}

	/**
	 * @param string $service
	 */
	public function setService($service)
	{
		$this->service = $service;
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
	 * @return PublicType
	 */
	public function getPublicCategory()
	{
		return $this->publicCategory;
	}

	/**
	 * @param PublicType $publicCategory
	 */
	public function setPublicCategory($publicCategory)
	{
		$this->publicCategory = $publicCategory;
	}

	/**
	 * @return string
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * @param string $position
	 */
	public function setPosition($position)
	{
		$this->position = $position;
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
