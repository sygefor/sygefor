<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Serializer;
use AppBundle\Form\Type\Trainer\TrainerType;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainer;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use NotificationBundle\Mailer\MailerRecipientInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="trainer")
 * @ORM\Entity
 * @UniqueEntity(fields={"email", "organization"}, message="Cette adresse email est déjà utilisée.", ignoreNull=true, groups={"Default", "trainer"})
 */
class Trainer extends AbstractTrainer implements MailerRecipientInterface
{
    use CoordinatesTrait;
    use ProfessionalSituationTrait;

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
    protected $isOrganization = true;

    /**
     * @var bool
     * @ORM\Column(name="is_public", type="boolean")
     * @Serializer\Groups({"trainer"})
     */
    protected $isPublic;

    /**
     * @var string
     * @ORM\Column(name="responsabilities", type="text", nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $responsabilities;

    public function __construct()
    {
        $this->isPublic = false;
        $this->addressType = 0;

        parent::__construct();
    }

    /**
     * Remove properties related to another organization, except excluded ones.
     */
    public function changePropertiesOrganization()
    {
        $excludedProperties = array('participations');

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
                    } elseif (method_exists($this->$key, 'getOrganization')) {
                        if ($this->$key->getOrganization() !== null && $this->$key->getOrganization() !== $this->getOrganization()) {
                            $this->$key = null;
                        }
                    }
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isArchived()
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
     * @return mixed
     */
    public static function getFormType()
    {
        return TrainerType::class;
    }

    /**
     * loadValidatorMetadata.
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        parent::loadValidatorMetadata($metadata);

        // CoordinateTrait
        $metadata->addPropertyConstraint('email', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un email.',
        )));
    }
}
