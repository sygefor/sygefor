<?php

namespace Sygefor\Bundle\TrainerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Serializer;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sygefor\Bundle\CoreBundle\AccessRight\SerializedAccessRights;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait\PersonTrait;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait\CoordinatesTrait;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait\ProfessionalSituationTrait;
use Sygefor\Bundle\TrainerBundle\Entity\Term\TrainerType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Sygefor\Bundle\TrainerBundle\Form\BaseTrainerType;

/**
 * Trainer
 *
 * @ORM\Table(name="trainer")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @UniqueEntity(fields={"email", "organization"}, message="Cette adresse email est déjà utilisée.", ignoreNull=true, groups={"Default", "trainer"})
 */
abstract class AbstractTrainer implements SerializedAccessRights
{
    use ORMBehaviors\Timestampable\Timestampable;
    use PersonTrait;
    use CoordinatesTrait;
    use ProfessionalSituationTrait;

    /**
     * @var int id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "trainer", "session", "api.training"})
     */
    protected $id;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $organization;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractParticipation", mappedBy="trainer", cascade={"remove"})
     * @Serializer\Exclude
     */
    protected $participations;

    /**
     * @var TrainerType
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainerBundle\Entity\Term\TrainerType")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $trainerType;

    /**
     * @var bool
     * @ORM\Column(name="is_archived", type="boolean", nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $isArchived;

    /**
     * @var bool
     * @ORM\Column(name="is_allow_send_mail", type="boolean", nullable=true)
     * @Serializer\Groups({"trainer", "api.training", "api.trainer"})
     */
    protected $isAllowSendMail = false;

    /**
     * @var bool
     * @ORM\Column(name="is_organization", type="boolean", nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $isOrganization;

    /**
     * @var bool
     * @ORM\Column(name="is_public", type="boolean")
     * @Serializer\Groups({"trainer"})
     */
    protected $isPublic;

    /**
     * @var string
     * @ORM\Column(name="observations", type="text", nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $observations;

    /**
     *
     */
    function __construct()
    {
        $this->participations = new ArrayCollection();
        $this->isPublic = false;
        $this->addressType = 0;
    }

    /**
     * Remove properties related to another organization, except excluded ones.
     */
    public function changePropertiesOrganization()
    {
        $excludedProperties = array('participations', 'institution');

        foreach (array_keys(get_object_vars($this)) as $key) {
            if (!in_array($key, $excludedProperties, true)) {
                if (is_object($this->$key)) {
                    if ($this->$key instanceof PersistentCollection) {
                        foreach ($this->$key as $item) {
                            if (is_object($item) && method_exists($item, 'getOrganization')) {
                                if ($item->getOrganization() !== null && $item->getOrganization() !== $this->getOrganization()) {
                                    $this->$key->removeElement($item);
                                }
                            }
                        }
                    } else if (method_exists($this->$key, 'getOrganization')) {
                        if ($this->$key->getOrganization() !== null && $this->$key->getOrganization() !== $this->getOrganization()) {
                            $this->$key = null;
                        }
                    }
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return ArrayCollection
     */
    public function getParticipations()
    {
        return $this->participations;
    }

    /**
     * @param ArrayCollection $participations
     */
    public function setParticipations($participations)
    {
        $this->participations = $participations;
    }

    /**
     * Return sessions from participations
     * Used to not to have update all publipost templates.
     *
     * @return ArrayCollection
     */
    public function getSessions()
    {
        $sessions = new ArrayCollection();
        foreach ($this->getParticipations() as $participation) {
            $sessions->add($participation->getSession());
        }

        return $sessions;
    }

    /**
     * @return TrainerType
     */
    public function getTrainerType()
    {
        return $this->trainerType;
    }

    /**
     * @param TrainerType $trainerType
     */
    public function setTrainerType($trainerType)
    {
        $this->trainerType = $trainerType;
    }

    /**
     * @return bool
     */
    public function isIsArchived()
    {
        return $this->isArchived;
    }

    /**
     * @param bool $isArchived
     */
    public function setIsArchived($isArchived)
    {
        $this->isArchived = $isArchived;
    }

    /**
     * @return bool
     */
    public function isIsAllowSendMail()
    {
        return $this->isAllowSendMail;
    }

    /**
     * @param bool $isAllowSendMail
     */
    public function setIsAllowSendMail($isAllowSendMail)
    {
        $this->isAllowSendMail = $isAllowSendMail;
    }

    /**
     * @return bool
     */
    public function getIsOrganization()
    {
        return $this->isOrganization;
    }

    /**
     * @param bool $isOrganization
     */
    public function setIsOrganization($isOrganization)
    {
        $this->isOrganization = $isOrganization;
    }

    /**
     * @return bool
     */
    public function isIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * @return string
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * @param string $observations
     */
    public function setObservations($observations)
    {
        $this->observations = $observations;
    }

    /**
     * loadValidatorMetadata.
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // PersonTrait
        $metadata->addPropertyConstraint('title', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner une civilité.',
        )));
        $metadata->addPropertyConstraint('firstName', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un prénom.',
        )));
        $metadata->addPropertyConstraint('lastName', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un nom de famille.',
        )));

        // CoordinateTrait
        $metadata->addPropertyConstraint('email', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un email.',
        )));
    }

    /**
     * @return mixed
     */
    static public function getFormType()
    {
        return BaseTrainerType::class;
    }

    /**
     * @return string
     */
    static public function getType()
    {
        return 'trainer';
    }
}
