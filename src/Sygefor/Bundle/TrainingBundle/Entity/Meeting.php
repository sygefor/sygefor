<?php

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Form\MeetingType;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="meeting")
 * traduction: rencontre
 */
class Meeting extends SingleSessionTraining
{
    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\EventType")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Vous devez préciser un type d'événement.")
     * @Serializer\Groups({"training", "api"})
     */
    protected $eventType;

    /**
     * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\EventKind")
     * @ORM\JoinTable(name="meeting__event_kind",
     *      joinColumns={@ORM\JoinColumn(name="meeting_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="event_kind_id", referencedColumnName="id")}
     * )
     * @Assert\NotBlank(message="Vous devez préciser la nature de l'événement.")
     * @Serializer\Groups({"training", "api"})
     */
    protected $eventKind;

    /**
     * @ORM\Column(name="national", type="boolean")
     * @var boolean
     * @Serializer\Groups({"training", "api"})
     */
    protected $national;

    /**
     * @ORM\Column(name="partners", type="string", length=1024, nullable=true)
     * @var String
     * @Serializer\Groups({"training", "api"})
     */
    protected $partners;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Serializer\Groups({"training"})
     */
    protected $receptionCost;

    /**
     * @var string $website
     * @ORM\Column(name="website", type="string", length=512, nullable=true)
     * @Serializer\Groups({"training", "api"})
     */
    protected $website;

    /**
     * @return string
     */
    static public function getType()
    {
        return 'meeting';
    }

    /**
     * @return string
     */
    static public function getTypeLabel()
    {
        return 'Rencontre scientifique';
    }

    /**
     * @return string
     */
    static public function getFormType()
    {
        return 'meetingtype';
    }

    /**
     * @param mixed $eventKind
     */
    public function setEventKind($eventKind)
    {
        $this->eventKind = $eventKind;
    }

    /**
     * @return mixed
     */
    public function getEventKind()
    {
        return $this->eventKind;
    }

    /**
     * @param mixed $eventType
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @return mixed
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param boolean $national
     */
    public function setNational($national)
    {
        $this->national = $national;
    }

    /**
     * @return boolean
     */
    public function getNational()
    {
        return $this->national;
    }

    /**
     * @param String $partners
     */
    public function setPartners($partners)
    {
        $this->partners = $partners;
    }

    /**
     * @return String
     */
    public function getPartners()
    {
        return $this->partners;
    }

    /**
     * @param mixed $receptionCost
     */
    public function setReceptionCost($receptionCost)
    {
        $this->receptionCost = $receptionCost;
    }

    /**
     * @return mixed
     */
    public function getReceptionCost()
    {
        return $this->receptionCost;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }
}
