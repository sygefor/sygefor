<?php
namespace Sygefor\Bundle\CoreBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait PersonTrait
 * @package Sygefor\Bundle\TraineeBundle\Entity
 */
trait PersonTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Term\Title")
     * @Assert\NotBlank(message="Vous devez renseigner une civilité.")
     * @Serializer\Groups({"trainee", "inscription", "trainer", "api.profile"})
     */
    protected $title;

    /**
     * @var string $firstName
     * @Assert\NotBlank(message="Vous devez renseigner un prénom.")
     * @ORM\Column(name="first_name", type="string", length=50)
     * @Serializer\Groups({"Default", "api.profile"})
     */
    protected $firstName;

    /**
     * @var string $lastName
     * @Assert\NotBlank(message="Vous devez renseigner un nom de famille.")
     * @ORM\Column(name="last_name", type="string", length=50)
     * @Serializer\Groups({"Default", "api.profile"})
     */
    protected $lastName;

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "api"})
     */
    public function getFullName()
    {
        return $this->getFirstName() . " " . $this->getLastName();
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->getFullName();
    }
}
