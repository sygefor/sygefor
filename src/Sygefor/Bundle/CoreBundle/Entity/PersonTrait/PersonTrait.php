<?php

namespace Sygefor\Bundle\CoreBundle\Entity\PersonTrait;

use JMS\Serializer\Annotation as Serializer;

/**
 * Trait PersonTrait.
 */
trait PersonTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\PersonTrait\Term\Title")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=50, nullable=true)
     * @Serializer\Groups({"Default", "api"})
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=50)
     * @Serializer\Groups({"Default", "api"})
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
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->getFullName();
    }
}
