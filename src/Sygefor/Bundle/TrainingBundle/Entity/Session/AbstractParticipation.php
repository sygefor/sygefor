<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Session;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TrainerBundle\Entity\AbstractTrainer;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Participation.
 *
 * @ORM\Entity
 * @ORM\Table(name="participation")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @UniqueEntity(fields={"session", "trainer"}, message="Ce formateur est déjà associé à cet évènement.")
 */
abstract class AbstractParticipation
{
    /**
     * @var int id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @var AbstractTrainer
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainerBundle\Entity\AbstractTrainer", inversedBy="participations")
     * @ORM\JoinColumn(name="trainer_id", referencedColumnName="id")
     * @Assert\NotNull(message="Vous devez sélectionner un formateur")
     * @Serializer\Groups({"participation", "session", "api.training"})
     */
    protected $trainer;

    /**
     * @var AbstractSession
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession", inversedBy="participations")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id")
     * @Assert\NotNull()
     * @Serializer\Groups({"participation", "trainer"})
     */
    protected $session;

    /**
     * @var bool
     * @ORM\Column(name="is_organization", type="boolean", nullable=true)
     * @Serializer\Groups({"participation"})
     */
    protected $isOrganization;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"Default", "api"})
     * @Serializer\Groups({"participation"})
     */
    protected $organization;

    /**
     * @return AbstractTrainer
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

    /**
     * @return mixed
     */
    static public function getFormType()
    {
        return 'Sygefor\Bundle\TrainingBundle\Form\ParticipationType';
    }

    /**
     * @return string
     */
    static public function getType()
    {
        return 'participation';
    }
}
