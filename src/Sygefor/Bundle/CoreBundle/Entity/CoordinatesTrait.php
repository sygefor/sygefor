<?php
namespace Sygefor\Bundle\CoreBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Trait CoordinatesTrait
 * @package Sygefor\Bundle\TraineeBundle\Entity
 */
trait CoordinatesTrait
{
    /**
     * @var boolean addressType
     *
     * @ORM\Column(name="address_type", type="integer")
     * @Serializer\Groups({"trainee", "trainer", "api.profile"})
     */
    protected $addressType;

    /**
     * @var string institutionName
     * @ORM\Column(name="institution_name", type="string", length=512, nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "api.profile", "inscription"})
     */
    protected $institutionName;

    /**
     * @var string address
     *
     * @Assert\NotBlank(message="Vous devez renseigner une adresse.", groups={"api.profile"})
     * @ORM\Column(name="address", type="string", length=512, nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "api.profile"})
     */
    protected $address;

    /**
     * @var string bp
     * @ORM\Column(name="bp", type="string", length=512, nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "api.profile"})
     */
    protected $bp;

    /**
     * @var string zip
     *
     * @Assert\NotBlank(message="Vous devez renseigner un code postal.", groups={"api.profile"})
     * @ORM\Column(name="zip", type="string", length=32, nullable=true)
     * @Serializer\Groups({"trainee", "inscription", "trainer", "api.profile"})
     */
    protected $zip;

    /**
     * @var string city
     *
     * @Assert\NotBlank(message="Vous devez renseigner une ville.", groups={"api.profile"})
     * @ORM\Column(name="city", type="string", length=128, nullable=true)
     * @Serializer\Groups({"trainee", "inscription", "trainer", "api.profile"})
     */
    protected $city;

    /**
     * @var string cedex
     * @ORM\Column(name="cedex", type="string", length=32, nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "api.profile"})
     */
    protected $cedex;

    /**
     * @var string $email
     * @Assert\NotBlank(message="Vous devez renseigner un email.", groups={"organization", "trainee"})
     * @Assert\Email(message="Vous devez renseigner un email valide.")
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     * @Serializer\Groups({"trainee", "inscription", "trainer", "api.profile", "api.token"})
     */
    protected $email;

    /**
     * @var string $phoneNumber
     *
     * @ORM\Column(name="phone_number", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainee", "inscription", "trainer", "api.profile"})
     */
    protected $phoneNumber;

    /**
     * @var string $phoneNumber
     *
     * @ORM\Column(name="fax_number", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainee", "trainer", "api.profile"})
     */
    protected $faxNumber;

    /**
     * @var string $website
     * @ORM\Column(name="website", type="string", length=512, nullable=true)
     * @Serializer\Groups({"trainer"})
     */
    protected $website;

    /**
     * Copy coordinates from another entity
     *
     * @param CoordinatesTrait $entity
     */
    public function copyCoordinates($entity)
    {
        $this->setAddress($entity->getAddress());
        $this->setAddressType($entity->getAddressType());
        $this->setBp($entity->getBp());
        $this->setCedex($entity->getCedex());
        $this->setCity($entity->getCity());
        $this->setInstitutionName($entity->getInstitutionName());
        $this->setPhoneNumber($entity->getPhoneNumber());
        $this->setFaxNumber($entity->getFaxNumber());
        $this->setZip($entity->getZip());
        $this->setEmail($entity->getEmail());
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param boolean $addressType
     */
    public function setAddressType($addressType)
    {
        $this->addressType = $addressType;
    }

    /**
     * @return boolean
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * @param string $bp
     */
    public function setBp($bp)
    {
        $this->bp = $bp;
    }

    /**
     * @return string
     */
    public function getBp()
    {
        return $this->bp;
    }

    /**
     * @param string $cedex
     */
    public function setCedex($cedex)
    {
        $this->cedex = $cedex;
    }

    /**
     * @return string
     */
    public function getCedex()
    {
        return $this->cedex;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $institutionName
     */
    public function setInstitutionName($institutionName)
    {
        $this->institutionName = $institutionName;
    }

    /**
     * @return string
     */
    public function getInstitutionName()
    {
        return $this->institutionName;
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
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @return string
     */
    public function getFaxNumber()
    {
        return $this->faxNumber;
    }

    /**
     * @param string $faxNumber
     */
    public function setFaxNumber($faxNumber)
    {
        $this->faxNumber = $faxNumber;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Return the full address
     *
     * @return string
     */
    public function getFullAddress()
    {
        $lines = array();
        if($this->getAddressType() && $this->getInstitutionName()) {
            $lines[] = $this->getInstitutionName();
        }
        if($this->getAddress()) {
            $lines[] = $this->getAddress();
        }
        if($this->getAddressType() && $this->getBp()) {
            $lines[] = $this->getBp();
        }
        if($this->getCity()) {
            $lines[] = ($this->getZip() ? $this->getZip() . " " : "") . $this->getCity();
        }
        return join("\n", $lines);
    }

    /**
     * @Assert\Callback(groups={"api.profile"})
     * Unable to use the Expression assert, due to https://github.com/symfony/symfony/pull/11590
     */
    public function addressTypeAssert(ExecutionContextInterface $context)
    {
        if($this->getAddressType()) {
            if(!$this->getInstitutionName()) {
                $context->addViolationAt('institutionName', "Vous devez renseigner un nom d'établissement.");
            }
        }
    }

}
