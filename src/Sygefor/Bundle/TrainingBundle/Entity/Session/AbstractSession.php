<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Session;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Sygefor\Bundle\InstitutionBundle\Entity\Term\GeographicOrigin;
use Sygefor\Bundle\InscriptionBundle\Entity\AbstractInscription;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\TrainingBundle\Entity\Material\Material;
use Sygefor\Bundle\TrainingBundle\Entity\Session\Term\Place;
use Sygefor\Bundle\TrainingBundle\Entity\Session\Term\SessionType;
use Sygefor\Bundle\CoreBundle\AccessRight\SerializedAccessRights;
use Sygefor\Bundle\TrainingBundle\Form\BaseSessionType;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Session.
 *
 * @ORM\Table(name="session")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\HasLifecycleCallbacks
 *
 * traduction: session
 */
abstract class AbstractSession implements SerializedAccessRights
{
    use ORMBehaviors\Timestampable\Timestampable;

    // registration states
    const REGISTRATION_DEACTIVATED = 0;
    const REGISTRATION_CLOSED      = 1;
    const REGISTRATION_PRIVATE     = 2;
    const REGISTRATION_PUBLIC      = 3;

    // registration states
    const STATUS_OPEN     = 0;
    const STATUS_REPORTED = 1;
    const STATUS_CANCELED = 2;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @var AbstractTraining
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining", inversedBy="sessions")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Serializer\Groups({"session", "inscription", "trainee", "trainer", "api"})
     */
    protected $training;

    /**
     * @ORM\OneToMany(targetEntity="AbstractParticipation", mappedBy="session", cascade={"remove"})
     * @Serializer\Groups({"session", "api.training"})
     */
    protected $participations;

    /**
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\InscriptionBundle\Entity\AbstractInscription", mappedBy="session", fetch="EXTRA_LAZY", cascade={"remove"})
     * @ORM\OrderBy({"createdAt" = "DESC"})
     * @Serializer\Groups({"session"})
     */
    protected $inscriptions;

    /**
     * @ORM\Column(name="promote", type="boolean")
     * @Serializer\Groups({"Default", "session", "api"})
     */
    protected $promote = false;

    /**
     * @ORM\Column(name="registration", type="integer")
     */
    protected $registration = self::REGISTRATION_CLOSED;

    /**
     * @ORM\Column(name="displayOnline", type="boolean")
     *
     * @var bool
     * @Serializer\Groups({"Default", "session", "api"})
     */
    protected $displayOnline = false;

    /**
     * @ORM\Column(name="dateBegin", type="datetime")
     * @Assert\NotBlank(message="Vous devez préciser une date de début.")
     * @Serializer\Groups({"Default", "session", "api"})
     */
    protected $dateBegin;

    /**
     * @ORM\Column(name="dateEnd", type="datetime", nullable=true)
     * @Serializer\Groups({"Default", "session", "api"})
     */
    protected $dateEnd;

    /**
     * @var SessionType
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session\Term\SessionType")
     * @ORM\JoinColumn(name="sessionType_id", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"session", "inscription", "api"})
     */
    protected $sessionType;

    /**
     * @ORM\Column(name="hourNumber", type="integer")
     * @Serializer\Groups({"session", "inscription", "api"})
     * @Assert\GreaterThan(value = 0, message = "Vous devez renseigner un nombre d'heures")
     * @Assert\NotNull(message="Vous devez renseigner un nombre d'heures")
     */
    protected $hourNumber;

    /**
     * @ORM\Column(name="dayNumber", type="integer")
     * @Serializer\Groups({"session", "inscription", "api"})
     * @Assert\GreaterThan(value = 0, message = "Vous devez renseigner un nombre de jours")
     * @Assert\NotNull(message="Vous devez renseigner un nombre de jours")
     */
    protected $dayNumber;

    /**
     * @ORM\Column(name="schedule", type="string", length=512, nullable=true)
     * @Serializer\Groups({"session", "inscription", "api"})
     */
    protected $schedule;

    /**
     * @var Place
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session\Term\Place", cascade={"persist"})
     * @ORM\JoinColumn(name="place_id", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"session", "inscription", "api"})
     */
    protected $place;

    /**
     * @ORM\Column(name="comments", type="text", nullable=true)
     * @var String
     * @Serializer\Groups({"session"})
     */
    protected $comments;

    /**
     * @ORM\Column(name="status", type="integer")
     * @Serializer\Groups({"session", "training", "inscription", "api"})
     */
    protected $status = self::STATUS_OPEN;

    /**
     * @ORM\Column(name="numberOfRegistrations", type="integer", nullable=true)
     * @Serializer\Exclude
     */
    protected $numberOfRegistrations;

    /**
     * @ORM\Column(name="maximumNumberOfRegistrations", type="integer")
     * @Serializer\Groups({"session", "training", "inscription", "api"})
     * @Assert\NotBlank()
     */
    protected $maximumNumberOfRegistrations = 20;

    /**
     * @ORM\Column(name="limitRegistrationDate", type="datetime")
     * @Serializer\Groups({"session", "training", "api"})
     */
    protected $limitRegistrationDate;

    /**
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session\ParticipantsSummary", mappedBy="session", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     * @Serializer\Groups({"session"})
     */
    protected $participantsSummaries;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Material\Material", mappedBy="session", cascade={"remove", "persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"training", "session", "api.attendance"})
     */
    protected $materials;

    /**
     * @var ArrayCollection
     * @Serializer\Groups({"api.attendance"})
     */
    protected $allMaterials;

    /**
     * @var string
     *             Used for session duplication
     */
    protected $inscriptionManagement;

    function __construct()
    {
        $this->inscriptions          = new ArrayCollection();
        $this->participations        = new ArrayCollection();
        $this->participantsSummaries = new ArrayCollection();
        $this->materials             = new ArrayCollection();
    }

    public function __clone()
    {
        $this->setId(null);
        $this->inscriptions          = new ArrayCollection();
        $this->participations        = new ArrayCollection();
        $this->participantsSummaries = new ArrayCollection();
        $this->materials             = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return AbstractTraining
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * @param AbstractTraining $training
     */
    public function setTraining($training)
    {
        $this->training = $training;
    }

    /**
     * @return mixed
     */
    public function getParticipations()
    {
        return $this->participations;
    }

    /**
     * @param mixed $participations
     */
    public function setParticipations($participations)
    {
        $this->participations = $participations;
    }

    /**
     * @param AbstractParticipation $participation
     *
     * @return bool
     */
    public function addParticipation($participation)
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);

            return true;
        }

        return false;
    }

    /**
     * @param AbstractParticipation $participation
     *
     * @return bool
     */
    public function removeParticipation($participation)
    {
        if ($this->participations->contains($participation)) {
            $this->participations->removeElement($participation);

            return true;
        }

        return false;
    }

    /**
     * HumanReadablePropertyAccessor helper function : allows to get a single string containing all trainers.
     *
     * @return string
     */
    public function getTrainersListString()
    {
        if ( ! $this->getParticipations()) {
            return '';
        }

        $array = array();
        /** @var AbstractParticipation $participation */
        foreach ($this->getParticipations() as $participation) {
            $array[] = $participation->getTrainer()->getFullName();
        }

        return implode(', ', $array);
    }

    /**
     * Return trainers from participations
     * Used for publipost templates.
     *
     * @return ArrayCollection
     */
    public function getTrainers()
    {
        $trainers = new ArrayCollection();
        /** @var AbstractParticipation $participation */
        foreach ($this->getParticipations() as $participation) {
            $trainers->add($participation->getTrainer());
        }

        return $trainers;
    }

    /**
     * @return mixed
     */
    public function getInscriptions()
    {
        return $this->inscriptions;
    }

    /**
     * @param mixed $inscriptions
     */
    public function setInscriptions($inscriptions)
    {
        $this->inscriptions = $inscriptions;
    }

    /**
     * @param AbstractInscription $inscription
     *
     * @return bool
     */
    public function addInscription($inscription)
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);

            return true;
        }

        return false;
    }

    /**
     * @param AbstractInscription $inscription
     *
     * @return bool
     */
    public function removeInscription($inscription)
    {
        if ($this->inscriptions->contains($inscription)) {
            $this->inscriptions->removeElement($inscription);

            return true;
        }

        return false;
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
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @param mixed $registration
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
    }

    /**
     * @return bool
     */
    public function isDisplayOnline()
    {
        return $this->displayOnline;
    }

    /**
     * @param bool $displayOnline
     */
    public function setDisplayOnline($displayOnline)
    {
        $this->displayOnline = $displayOnline;
    }

    /**
     * @return mixed
     */
    public function getDateBegin()
    {
        return $this->dateBegin;
    }

    /**
     * @return int
     * @Serializer\VirtualProperty
     */
    public function getYear()
    {
        return $this->getDateBegin() ? $this->getDateBegin()->format('Y') : null;
    }

    /**
     * @return int
     * @Serializer\VirtualProperty
     */
    public function getSemester()
    {
        return $this->getDateBegin() ? ceil($this->getDateBegin()->format('m') / 6) : null;
    }

    /**
     * @return int
     * @Serializer\VirtualProperty
     * @Serializer\Groups("api")
     */
    public function getSemesterLabel()
    {
        return $this->getYear() . ' - ' . ($this->getSemester() < 2 ? '1er' : '2nd') . ' semestre ';
    }

    /**
     * @param mixed $dateBegin
     */
    public function setDateBegin($dateBegin)
    {
        $this->dateBegin = $dateBegin;
    }

    /**
     * @return mixed
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param mixed $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
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
     * Get date range for OpenTBS.
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "session", "api"})
     *
     * @return string
     */
    public function getDateRange()
    {
        if ( ! $this->dateBegin) {
            return '';
        }
        if ( ! $this->dateEnd || $this->dateBegin->format('d/m/y') === $this->dateEnd->format('d/m/y')) {
            return 'le ' . $this->dateBegin->format('d/m/Y');
        }

        return 'du ' . $this->dateBegin->format('d/m/Y') . ' au ' . $this->dateEnd->format('d/m/Y');
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
     * @return string
     */
    public function getDuration()
    {
        return $this->getHourNumber() . ' heure(s) sur ' . $this->getDayNumber() . ' jour(s)';
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
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getMaximumNumberOfRegistrations()
    {
        return $this->maximumNumberOfRegistrations;
    }

    /**
     * @param mixed $maximumNumberOfRegistrations
     */
    public function setMaximumNumberOfRegistrations($maximumNumberOfRegistrations)
    {
        $this->maximumNumberOfRegistrations = $maximumNumberOfRegistrations;
    }

    /**
     * Return true if the session is available on the website (private or public registration).
     *
     * @return mixed
     */
    public function isAvailable()
    {
        return $this->registration > self::REGISTRATION_CLOSED;
    }

    /**
     * Return true if the session registration is public.
     *
     * @return mixed
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"api"})
     */
    public function isPublic()
    {
        return $this->registration === self::REGISTRATION_PUBLIC;
    }

    /**
     * The session is registrable.
     */
    public function isRegistrable()
    {
        if ($this->getStatus() !== self::STATUS_OPEN) {
            return false;
        }

        $now = new \DateTime();

        // check date
        if ($this->getDateBegin() <= $now) {
            return false;
        }

        // check status
        if ($this->getRegistration() < self::REGISTRATION_PRIVATE) {
            return false;
        }

        // check limit registration date
        if ($this->getLimitRegistrationDate() && $this->getLimitRegistrationDate() < $now) {
            return false;
        }

        // else
        return true;
    }

    /**
     * hack : for serialization.
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session", "training", "api.training"})
     */
    public function registrable()
    {
        return $this->isRegistrable();
    }

    /**
     * Return available places.
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"api.training"})
     */
    public function getAvailablePlaces()
    {
        return $this->getMaximumNumberOfRegistrations() - $this->getNumberOfAcceptedRegistrations();
    }

    /**
     * @return mixed
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session", "training"})
     */
    public function getNumberOfRegistrations()
    {
        if ($this->getRegistration() === self::REGISTRATION_DEACTIVATED) {
            return $this->numberOfRegistrations;
        }

        if (empty($this->inscriptions)) {
            return 0;
        }

        return $this->inscriptions->count();
    }

    /**
     * @param mixed $numberOfRegistrations
     */
    public function setNumberOfRegistrations($numberOfRegistrations)
    {
        $this->numberOfRegistrations = $numberOfRegistrations;
    }

    /**
     * @return mixed
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session", "training"})
     */
    public function getNumberOfAcceptedRegistrations()
    {
        if($this->getRegistration() === self::REGISTRATION_DEACTIVATED) {
            return $this->numberOfRegistrations;
        }

        if (empty($this->inscriptions)) {
            return 0;
        }

        $nAccepted = 0;
        foreach ($this->inscriptions as $inscription) {
            if ($inscription->getInscriptionStatus()->getStatus() === InscriptionStatus::STATUS_ACCEPTED){
                ++$nAccepted;
            }
        }

        return $nAccepted;
    }

    /**
     * @return mixed
     */
    public function getLimitRegistrationDate()
    {
        return $this->limitRegistrationDate;
    }

    /**
     * @param mixed $limitRegistrationDate
     */
    public function setLimitRegistrationDate($limitRegistrationDate)
    {
        $this->limitRegistrationDate = $limitRegistrationDate;
    }

    /**
     * Update the limit registration date.
     *
     * @ORM\PrePersist
     */
    public function updateLimitRegistrationDate()
    {
        // if the limit registration date is not set,
        // set it to the day before date begin
        if (!$this->getLimitRegistrationDate()) {
            $date = clone $this->getDateBegin();
            $date->modify('-1 day');
            $this->setLimitRegistrationDate($date);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getMaterials()
    {
        return $this->materials;
    }

    /**
     * @param ArrayCollection $materials
     */
    public function setMaterials($materials)
    {
        $this->materials = $materials;
    }

    /**
     * @param Material $material
     *
     * @return bool
     */
    public function addMaterial($material)
    {
        if (!$this->materials->contains($material)) {
            $material->setSession($this);
            $this->materials->add($material);

            return true;
        }

        return false;
    }

    /**
     * @return ArrayCollection
     */
    public function getAllMaterials()
    {
        return $this->allMaterials;
    }

    /**
     * @param ArrayCollection $allMaterials
     */
    public function setAllMaterials($allMaterials)
    {
        $this->allMaterials = $allMaterials;
    }

    /**
     * @return mixed
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session", "training"})
     */
    public function getNumberOfParticipants()
    {
        $count = 0;
        if ($this->getRegistration() === self::REGISTRATION_DEACTIVATED) {
            foreach ($this->getParticipantsSummaries() as $summary) {
                $count += $summary->getCount();
            }
        }
        else {
            /** @var AbstractInscription $inscription */
            foreach ($this->getInscriptions() as $inscription) {
                if ($inscription->getPresenceStatus() && $inscription->getPresenceStatus()->getStatus() === PresenceStatus::STATUS_PRESENT) {
                    ++$count;
                }
            }
        }

        return $count;
    }

    /**
     * @return ArrayCollection
     */
    public function getParticipantsSummaries()
    {
        return $this->participantsSummaries;
    }

    /**
     * @param ArrayCollection $participantsSummaries
     */
    public function setParticipantsSummaries($participantsSummaries)
    {
        /** @var ParticipantsSummary $summary */
        foreach ($participantsSummaries as $summary) {
            $summary->setSession($this);
        }
        $this->participantsSummaries = $participantsSummaries;
    }

    /**
     * @param ParticipantsSummary $participantsSummary
     *
     * @return bool
     */
    public function addParticipantsSummary($participantsSummary)
    {
        foreach ($this->participantsSummaries as $participantsSummaryOne) {
            if ($participantsSummaryOne->getPublicType() === $participantsSummary->getPublicType() &&
                $participantsSummaryOne->getSession() === $participantsSummary->getSession()) {
                $participantsSummaryOne->setCount($participantsSummaryOne->getCount() + $participantsSummary->getCount());

                return false;
            }
        }

        $participantsSummary->setSession($this);
        $this->participantsSummaries->add($participantsSummary);

        return true;
    }

    /**
     * @param ParticipantsSummary $participantsSummary
     *
     * @return bool
     */
    public function removeParticipantsSummary($participantsSummary)
    {
        if ($this->participantsSummaries->contains($participantsSummary)) {
            $this->participantsSummaries->removeElement($participantsSummary);

            return true;
        }

        return false;
    }

    /**
     * Return participants stats for ActivityReport.
     */
    public function getParticipantsStats()
    {
        $stats = array();

        // helper function
        $getStat = function ($publicType, $geographicOrigin = null) use (&$stats) {
            $hash = array(
                $publicType->getId(),
                $geographicOrigin ? $geographicOrigin->getId() : 0,
            );
            $id = implode('-', $hash);
            if (isset($stats[$id])) {
                return $stats[$id];
            }
            $stat = new ParticipantsStat();
            $stat->setSession($this);
            $stat->setPublicType($publicType);
            $stat->setGeographicOrigin($geographicOrigin);
            $stats[$id] = $stat;

            return $stat;
        };

        if ($this->getRegistration() > self::REGISTRATION_DEACTIVATED) {
            // if the registration is not deactivated, get info from inscriptions
            /** @var AbstractInscription $inscription */
            foreach ($this->getInscriptions() as $inscription) {
                if ($inscription->getPresenceStatus() && $inscription->getPresenceStatus()->getStatus() === PresenceStatus::STATUS_PRESENT) {
                    $geographicOrigin = $this->getGeographicOriginFromInscription($inscription);
                    $publicType       = $inscription->getPublicType();
                    $getStat($publicType, $geographicOrigin)->incrementCount();
                }
            }
        }
        // get it from participants summaries
        else {
            foreach ($this->getParticipantsSummaries() as $summary) {
                $geographicOrigin = $this->getGeographicOriginFromInscription();
                $publicType       = $summary->getPublicType();
                if ($summary->getCount()) {
                    $getStat($publicType, $geographicOrigin)->setCount($summary->getCount());
                }
            }
        }

        return $stats;
    }

    /**
     * Try to return geographic origin for a given inscription, from training otherwise.
     *
     * @param AbstractInscription $inscription
     *
     * @return GeographicOrigin
     */
    function getGeographicOriginFromInscription($inscription = null)
    {
        /** @var AbstractInstitution $institution */
        $institution = null;

        // fetch institution...
        if ($inscription && $inscription->getInstitution()) {
            // ... from inscription
            $institution = $inscription->getInstitution();
        }
        else if ($inscription && $inscription->getTrainee()->getInstitution()) {
            // ... from trainee
            $institution = $inscription->getTrainee()->getInstitution();
        }
        else if (method_exists($this->getTraining(), 'getInstitution') && $this->getTraining()->getInstitution()) {
            // ... from training
            $institution = $this->getTraining()->getInstitution();
        }
        else {
            // impossible à gérer
            // cf transformer : "Autre"
        }

        if ($institution) {
            return $institution->getGeographicOrigin();
        }

        return;
    }

    /**
     * @return string
     */
    public function getInscriptionManagement()
    {
        return $this->inscriptionManagement;
    }

    /**
     * @param string $inscriptionManagement
     */
    public function setInscriptionManagement($inscriptionManagement)
    {
        $this->inscriptionManagement = $inscriptionManagement;
    }

    /**
     * @Serializer\VirtualProperty
     *
     * @param $front_root_url
     * @param $apiSerialization
     *
     * @return string
     * @return string
     */
    public function getFrontUrl($front_root_url = 'http://sygefor.dev', $apiSerialization = false)
    {
        $url = $front_root_url . '/training/' . $this->getTraining()->getId() . '/';
        if (!$apiSerialization) {
            // URL permitting to register a private session
            if ($this->getRegistration() === self::REGISTRATION_PRIVATE) {
                return $url . $this->getId() . '/' . md5($this->getId() + $this->getTraining()->getId());
            }
        }

        // return public URL
        return $url . $this->getId();
    }

    function __toString()
    {
        return $this->getTraining()->getName() . " - " . $this->getDateRange();
    }

    /**
     * @return mixed
     */
    static public function getFormType()
    {
        return BaseSessionType::class;
    }

    /**
     * @return string
     */
    static public function getType()
    {
        return 'session';
    }
}
