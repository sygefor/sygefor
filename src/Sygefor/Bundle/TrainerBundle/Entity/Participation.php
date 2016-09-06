<?php

namespace Sygefor\Bundle\TrainerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Participation
 *
 * @ORM\Table(name="participation")
 * @ORM\Entity
 */
class Participation
{
    /**
     * @var integer id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @var Trainer $trainer
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainerBundle\Entity\Trainer", inversedBy="participations")
     * @ORM\JoinColumn(name="trainer_id", referencedColumnName="id")
     * @Assert\NotNull(message="Vous devez sélectionner un formateur")
     * @Serializer\Groups({"participation", "session", "api.training"})
     */
    protected $trainer;

    /**
     * @var Session $session
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session", inversedBy="participations")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id")
     * @Assert\NotNull()
     * @Serializer\Groups({"participation", "trainer"})
     */
    protected $session;

    /**
     * @var boolean $isUrfist
     * @ORM\Column(name="is_urfist", type="boolean", nullable=true)
     * @Serializer\Groups({"participation"})
     */
    protected $isUrfist;

    /**
     * @var Organization $organization
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"Default", "api"})
     * @Serializer\Groups({"participation"})
     */
    protected $organization;

    /**
     * @return mixed
     */
    public function getTrainer()
    {
        return $this->trainer;
    }

    /**
     * @param mixed $trainer
     */
    public function setTrainer($trainer)
    {
        $this->trainer = $trainer;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param mixed $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * @return mixed
     */
    public function getIsUrfist()
    {
        return $this->isUrfist;
    }

    /**
     * @param mixed $isUrfist
     */
    public function setIsUrfist($isUrfist)
    {
        $this->isUrfist = $isUrfist;
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getIsLocal()
    {
        return $this->session->getTraining()->getOrganization()->getId() === $this->organization->getId();
    }
}