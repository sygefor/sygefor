<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Material;


use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Material.
 *
 * @ORM\Entity
 * @ORM\Table(name="material")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({})
 * @ORM\InheritanceType("JOINED")
 */
abstract class Material
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $name;

    /**
     * @var AbstractTraining
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining", inversedBy="materials")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Exclude
     */
    protected $training;

    /**
     * @var AbstractSession
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession", inversedBy="materials")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Exclude
     */
    protected $session;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Material
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param AbstractTraining $training
     */
    public function setTraining($training = null)
    {
        $this->training = $training;
    }

    /**
     * @return AbstractTraining
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * @return AbstractSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param AbstractSession $session
     */
    public function setSession($session = null)
    {
        $this->session = $session;
    }
}
