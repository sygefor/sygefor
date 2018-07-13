<?php

namespace AppBundle\Entity\Session;

use AppBundle\Entity\Organization;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\AbstractParticipation;
use AppBundle\Form\Type\Session\ParticipationType;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="participation")
 * @ORM\Entity
 */
class Participation extends AbstractParticipation
{
    /**
     * @var bool
     * @ORM\Column(name="is_organization", type="boolean", nullable=true)
     * @Serializer\Groups({"participation"})
     */
    protected $isOrganization;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"Default", "api"})
     * @Serializer\Groups({"participation"})
     */
    protected $organization;

    /**
     * @return mixed
     */
    public function getIsOrganization()
    {
        return $this->isOrganization;
    }

    /**
     * @param mixed $isOrganization
     */
    public function setIsOrganization($isOrganization)
    {
        $this->isOrganization = $isOrganization;
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
     * @return bool
     */
    public function getIsLocal()
    {
        return $this->session->getTraining()->getOrganization()->getId() === $this->organization->getId();
    }

    /**
     * @return mixed
     */
    public static function getFormType()
    {
        return ParticipationType::class;
    }
}
