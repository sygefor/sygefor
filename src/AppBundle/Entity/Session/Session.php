<?php

namespace AppBundle\Entity\Session;

use AppBundle\Entity\Training\Module;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Term\Session\Place;
use JMS\Serializer\Annotation as Serializer;
use AppBundle\Form\Type\Session\SessionType;
use Doctrine\Common\Collections\ArrayCollection;
use Sygefor\Bundle\CoreBundle\Entity\AbstractSession;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="session")
 * @ORM\Entity
 */
class Session extends AbstractSession
{
    use BalanceTrait;
    use SummaryTrait;
    use SessionPublipost;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Serializer\Groups({"training", "session", "inscription", "api"})
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="date_precisions", type="string", length=255, nullable=true)
     * @Serializer\Groups({"training", "session", "inscription", "api"})
     */
    protected $datePrecisions;

    /**
     * @ORM\Column(name="price", type="text", nullable=true)
     * @Serializer\Groups({"training", "session", "inscription", "api"})
     */
    protected $price;

    /**
     * @ORM\Column(name="promote", type="boolean")
     * @Serializer\Groups({"Default", "session", "api"})
     */
    protected $promote = false;

    /**
     * @ORM\Column(name="hourNumber", type="float", nullable=true)
     * @Serializer\Groups({"training", "session", "inscription", "api"})
     */
    protected $hourNumber;

    /**
     * @ORM\Column(name="dayNumber", type="float", nullable=true)
     * @Serializer\Groups({"training", "session", "inscription", "api"})
     */
    protected $dayNumber;

    /**
     * @ORM\Column(name="schedule", type="string", length=512, nullable=true)
     * @Serializer\Groups({"training", "session", "inscription", "api"})
     */
    protected $schedule;

    /**
     * @var Place
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Term\Session\Place", cascade={"persist"})
     * @ORM\JoinColumn(name="place_id", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"training", "session", "inscription", "api"})
     */
    protected $place;

    /**
     * @var string
     * @ORM\Column(name="room", type="string", length=255)
     * @Serializer\Groups({"training", "training", "session", "inscription", "api"})
     */
    protected $room;

    /**
     * @var SessionType
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Term\Session\SessionType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"session", "inscription", "api"})
     */
    protected $sessionType;

    /**
     * @var Module
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Training\Module", inversedBy="sessions")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @Serializer\Groups({"session", "api.training", "api.inscription"})
     */
    protected $module;

    /**
     * Used for session creation form only.
     *
     * @var Module
     */
    protected $newModule;

    public function __construct()
    {
        $this->participantsSummaries = new ArrayCollection();

        parent::__construct();
    }

    public function __clone()
    {
        $this->participantsSummaries = new ArrayCollection();

        parent::__clone();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDatePrecisions()
    {
        return $this->datePrecisions;
    }

    /**
     * @param mixed $datePrecisions
     */
    public function setDatePrecisions($datePrecisions)
    {
        $this->datePrecisions = $datePrecisions;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getPromote()
    {
        return $this->promote;
    }

    /**
     * @param mixed $promote
     */
    public function setPromote($promote)
    {
        $this->promote = $promote;
    }

    /**
     * @return mixed
     */
    public function getHourNumber()
    {
        return $this->hourNumber;
    }

    /**
     * @param mixed $hourNumber
     */
    public function setHourNumber($hourNumber)
    {
        $this->hourNumber = $hourNumber;
    }

    /**
     * @return mixed
     */
    public function getDayNumber()
    {
        return $this->dayNumber;
    }

    /**
     * @param mixed $dayNumber
     */
    public function setDayNumber($dayNumber)
    {
        $this->dayNumber = $dayNumber;
    }

    /**
     * @return mixed
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @param mixed $schedule
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        if ($this->getHourNumber() !== null && $this->getDayNumber() !== null) {
            return $this->getHourNumber().' heure(s) sur '.$this->getDayNumber().' jour(s)';
        } elseif ($this->getHourNumber() !== null && $this->getDayNumber() === null) {
            return $this->getHourNumber().' heure(s)';
        } elseif ($this->getHourNumber() === null && $this->getDayNumber() !== null) {
            return $this->getDayNumber().' jour(s)';
        }

        return '';
    }

    /**
     * @return Place
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param Place $place
     */
    public function setPlace($place)
    {
        $this->place = $place;
    }

    /**
     * @return string
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param string $room
     */
    public function setRoom($room)
    {
        $this->room = $room;
    }

    /**
     * @return SessionType
     */
    public function getSessionType()
    {
        return $this->sessionType;
    }

    /**
     * @param SessionType $sessionType
     */
    public function setSessionType($sessionType)
    {
        $this->sessionType = $sessionType;
    }



    /**
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param Module $module
     */
    public function setModule($module)
    {
        $this->module = $module;
        if ($module) {
            $this->training->addModule($module);
        }
    }

    /**
     * @return Module
     */
    public function getNewModule()
    {
        return $this->newModule;
    }

    /**
     * @param Module $newModule
     */
    public function setNewModule($newModule)
    {
        $this->newModule = $newModule;
    }

    /**
     * @return mixed
     */
    public static function getFormType()
    {
        return SessionType::class;
    }

    /**
     * @Assert\Callback()
     *
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        // date end > date begin
        if ($this->dateEnd && $this->dateEnd->format('Y-m-d') < $this->dateBegin->format('Y-m-d')) {
            $context
                ->buildViolation("La date de fin ne peut pas être antérieure à la date de début")
                ->atPath('dayNumber')
                ->addViolation();
        }

        // limit registration date > date begin
        if ($this->dateBegin && $this->limitRegistrationDate && $this->dateBegin->format('Y-m-d') <= $this->limitRegistrationDate->format('Y-m-d')) {
            $context
                ->buildViolation("La date de limite d'inscription doit être antérieure au début de la session")
                ->atPath('limitRegistrationDate')
                ->addViolation();
        }
    }
}
