<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/9/16
 * Time: 9:48 AM
 */

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Sygefor\Bundle\MyCompanyBundle\Form\CorrespondentType;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractCorrespondent;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 *
 * @ORM\Table(name="correspondent")
 * @ORM\Entity
 */
class Correspondent extends AbstractCorrespondent
{
    /**
     * @var AbstractInstitution
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\MyCompanyBundle\Entity\Institution", inversedBy="trainingCorrespondents")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     * @Serializer\Groups({"trainee", "institution", "api.institution"})
     */
    protected $institution;

    /**
     * Used if form for new empty service.
     *
     * @var bool
     */
    protected $new = false;

    /**
     * @var string
     * @ORM\Column(name="position", type="string", length=512, nullable=true)
     * @Serializer\Groups({"trainee", "institution", "api.institution"})
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainee", "institution", "api.institution"})
     */
    protected $phoneNumber;

    /**
     * @var string
     * @Assert\NotBlank(message="Vous devez renseigner un email.")
     * @Assert\Email(message="Vous devez renseigner un email valide.")
     * @ORM\Column(name="email", type="string", length=128)
     * @Serializer\Groups({"trainee", "institution", "api.institution"})
     */
    protected $email;

    /**
     * @var string address
     *
     * @ORM\Column(name="address", type="string", length=512, nullable=true)
     * @Serializer\Groups({"trainee", "institution", "api.institution"})
     */
    protected $address;

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
    public function isNew()
    {
        return $this->new;
    }

    /**
     * @param bool $new
     */
    public function setNew($new)
    {
        $this->new = $new;
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
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->lastName) && empty($this->firstName) && empty($this->phoneNumber) && empty($this->email);
    }

    /**
     * loadValidatorMetadata.
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // PersonTrait
        $metadata->addPropertyConstraint('firstName', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un nom de famille.',
        )));
        $metadata->addPropertyConstraint('lastName', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un nom de famille.',
        )));
        $metadata->addPropertyConstraint('email', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un email.',
        )));
    }

    public static function getFormType()
    {
        return CorrespondentType::class;
    }
}