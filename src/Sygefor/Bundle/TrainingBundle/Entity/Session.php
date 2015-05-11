<?php

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Place;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use JMS\Serializer\Annotation as Serializer;

/**
 * Session
 *
 * @ORM\Table(name="session")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * traduction: session
 *
 */
class Session implements SerializedAccessRights
{
    use ORMBehaviors\Timestampable\Timestampable;

    // registration states
    const REGISTRATION_DEACTIVATED = 0;
    const REGISTRATION_CLOSED = 1;
    const REGISTRATION_PRIVATE = 2;
    const REGISTRATION_PUBLIC = 3;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @var Training
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Training", inversedBy="sessions")
     * @Serializer\Groups({"session", "inscription", "trainee", "trainer", "api"})
     */
    protected $training;

    /**
     * @ORM\Column(name="dateBegin", type="datetime")
     * @Assert\NotBlank(message="Vous devez préciser une date de début.")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $dateBegin;

    /**
     * @ORM\Column(name="dateEnd", type="datetime", nullable=true)
     * @Serializer\Groups({"Default", "api"})
     */
    protected $dateEnd;

    /**
     * @ORM\ManyToMany(targetEntity="Sygefor\Bundle\TrainerBundle\Entity\Trainer", inversedBy="sessions")
     * @ORM\JoinTable(name="session__session_trainers",
     *      joinColumns={@ORM\JoinColumn(name="session_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="trainer_id", referencedColumnName="id")}
     * )
     * @Serializer\Groups({"session", "training", "api.training"})
     */
    protected $trainers;

    /**
     * @ORM\Column(name="limitRegistrationDate", type="datetime")
     * @Serializer\Groups({"session", "training", "api"})
     */
    protected $limitRegistrationDate;

    /**
     * @ORM\Column(name="hourDuration", type="integer", nullable=true)
     * @Serializer\Groups({"session", "inscription", "api"})
     */
    protected $hourDuration;

    /**
     * @ORM\Column(name="schedule", type="string", length=512, nullable=true)
     * @Serializer\Groups({"session", "inscription", "api"})
     */
    protected $schedule;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Serializer\Groups({"session", "inscription", "api"})
     */
    protected $price;

    /**
     * @ORM\Column(name="registration", type="integer")
     */
    protected $registration = self::REGISTRATION_CLOSED;

    /**
     * @ORM\Column(name="maximumNumberOfRegistrations", type="integer")
     * @Serializer\Groups({"session", "training", "inscription", "api"})
     * @Assert\NotBlank()
     */
    protected $maximumNumberOfRegistrations = 20;

    /**
     * @ORM\Column(name="numberOfRegistrations", type="integer", nullable=true)
     * @Serializer\Exclude
     */
    protected $numberOfRegistrations;

    /**
     * @ORM\Column(name="numberOfPresentPeople", type="integer", nullable=true)
     */
    //protected $numberOfPresentPeople;

    /**
     * @var Place
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\Place", cascade={"persist"})
     * @Serializer\Groups({"session", "inscription", "api"})
     */
    protected $place;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $networkTrainerCost;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $externTrainerCost;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $externTrainerConsideration;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $reprographyCost;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $otherCost;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $subscriptionRightTaking;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $otherTaking;

    /**
     * @ORM\Column(name="observations", type="text", nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $observations;

    /**
     * @ORM\Column(name="comments", type="text", nullable=true)
     * @var String
     * @Serializer\Groups({"session"})
     */
    protected $comments;

    /**
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Inscription", mappedBy="session", fetch="EXTRA_LAZY", cascade={"remove"})
     * @ORM\OrderBy({"createdAt" = "DESC"})
     * @Serializer\Groups({"session"})
     */
    protected $inscriptions;

    /**
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\ParticipantsSummary", mappedBy="session", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     * @Serializer\Groups({"session"})
     */
    protected $participantsSummaries;

    /**
     * @ORM\Column(name="promote", type="boolean")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $promote = false;

    /**
     *
     */
    function __construct() {
        $this->trainers = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
        $this->participantsSummaries = new ArrayCollection();
    }

    /**
     * cloning function
     */
    public function __clone() {
        $this->setId(null) ;
        $this->trainers = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
    }

    /**
     * @return integer
     * @Serializer\VirtualProperty
     */
    public function getYear()
    {
        return $this->getDateBegin() ? $this->getDateBegin()->format("Y") : null;
    }

    /**
     * @return integer
     * @Serializer\VirtualProperty
     */
    public function getSemester()
    {
        return $this->getDateBegin() ? ceil($this->getDateBegin()->format("m")/6) : null;
    }

    /**
     * @return integer
     * @Serializer\VirtualProperty
     * @Serializer\Groups("api")
     */
    public function getSemesterLabel()
    {
        return $this->getYear() . " - " . ($this->getSemester() < 2 ? "1er" : "2nd") . " semestre ";
    }

    /**
     * @param mixed $externTrainerConsideration
     */
    public function setExternTrainerConsideration($externTrainerConsideration)
    {
        $this->externTrainerConsideration = $externTrainerConsideration;
    }

    /**
     * @return mixed
     */
    public function getExternTrainerConsideration()
    {
        return $this->externTrainerConsideration;
    }

    /**
     * @param mixed $externTrainerCost
     */
    public function setExternTrainerCost($externTrainerCost)
    {
        $this->externTrainerCost = $externTrainerCost;
    }

    /**
     * @return mixed $externTrainerCost
     */
    public function getExternTrainerCost()
    {
        return $this->externTrainerCost;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $limitRegistrationDate
     */
    public function setLimitRegistrationDate($limitRegistrationDate)
    {
        $this->limitRegistrationDate = $limitRegistrationDate;
    }

    /**
     * @return mixed
     */
    public function getLimitRegistrationDate()
    {
        return $this->limitRegistrationDate;
    }

    /**
     * @param mixed $maximumNumberOfRegistrations
     */
    public function setMaximumNumberOfRegistrations($maximumNumberOfRegistrations)
    {
        $this->maximumNumberOfRegistrations = $maximumNumberOfRegistrations;
    }

    /**
     * @return mixed
     */
    public function getMaximumNumberOfRegistrations()
    {
        return $this->maximumNumberOfRegistrations;
    }

    /**
     * @param mixed $hourDuration
     */
    public function setHourDuration($hourDuration)
    {
        $this->hourDuration = $hourDuration;
    }

    /**
     * @return mixed
     */
    public function getHourDuration()
    {
        return $this->hourDuration;
    }

    /**
     * @param mixed $networkTrainerCost
     */
    public function setNetworkTrainerCost($networkTrainerCost)
    {
        $this->networkTrainerCost = $networkTrainerCost;
    }

    /**
     * @return mixed
     */
    public function getNetworkTrainerCost()
    {
        return $this->networkTrainerCost;
    }

    /**
     * @param mixed $numberOfPresentPeople
     */
//    public function setNumberOfPresentPeople($numberOfPresentPeople)
//    {
//        $this->numberOfPresentPeople = $numberOfPresentPeople;
//    }

    /**
     * @return mixed
     */
//    public function getNumberOfPresentPeople()
//    {
//        return $this->numberOfPresentPeople;
//    }

    /**
     * @return mixed
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session", "training"})
     */
    public function getNumberOfParticipants()
    {
        $count = 0;
        if($this->getRegistration() == self::REGISTRATION_DEACTIVATED) {
            foreach($this->getParticipantsSummaries() as $summary) {
                $count += $summary->getCount();
            }
        } else {
            foreach($this->getInscriptions() as $inscription) {
                if($inscription->getPresenceStatus() && $inscription->getPresenceStatus()->getStatus() == PresenceStatus::STATUS_PRESENT) {
                    $count++;
                }
            }
        }
        return $count;
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
    public function getNumberOfRegistrations()
    {
        if($this->getRegistration() == self::REGISTRATION_DEACTIVATED) {
            return $this->numberOfRegistrations;
        }
        // @todo performance
        if (empty($this->inscriptions)) return 0;
        return $this->inscriptions->count();
    }

    /**
     * @return mixed
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session", "training"})
     */
    public function getNumberOfAcceptedRegistrations()
    {
        if($this->getRegistration() == self::REGISTRATION_DEACTIVATED) {
            return $this->numberOfRegistrations;
        }
        // @todo performance
        if (empty($this->inscriptions)) return 0;

        $nAccepted = 0;
        foreach ($this->inscriptions as $inscription) {
            if ($inscription->getInscriptionStatus()->getStatus()==InscriptionStatus::STATUS_ACCEPTED){
                $nAccepted++;
            }
        }

        return $nAccepted;
    }


    /**
     * @param mixed $observations
     */
    public function setObservations($observations)
    {
        $this->observations = $observations;
    }

    /**
     * @return mixed
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * @param mixed $otherCost
     */
    public function setOtherCost($otherCost)
    {
        $this->otherCost = $otherCost;
    }

    /**
     * @return mixed
     */
    public function getOtherCost()
    {
        return $this->otherCost;
    }

    /**
     * @return float
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session"})
     */
    public function getTotalCost()
    {
        return $this->networkTrainerCost + $this->externTrainerCost + $this->externTrainerConsideration + $this->reprographyCost + $this->otherCost;
    }

    /**
     * @param mixed $otherTaking
     */
    public function setOtherTaking($otherTaking)
    {
        $this->otherTaking = $otherTaking;
    }

    /**
     * @return mixed
     */
    public function getOtherTaking()
    {
        return $this->otherTaking;
    }

    /**
     * @return float
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session"})
     */
    public function getTotalTaking()
    {
        return $this->subscriptionRightTaking + $this->otherTaking;
    }

    /**
     * @param Place $place
     */
    public function setPlace(Place $place)
    {
        $this->place = $place;
    }

    /**
     * @return Place
     */
    public function getPlace()
    {
        return $this->place;
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
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $registration
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
    }

    /**
     * @return mixed
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Return true if the session is available on the website (private or public registration)
     * @return mixed
     */
    public function isAvailable()
    {
        return $this->registration > Session::REGISTRATION_CLOSED;
    }

    /**
     * Return true if the session registration is public
     *
     * @return mixed
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"api"})
     */
    public function isPublic()
    {
        return $this->registration == Session::REGISTRATION_PUBLIC;
    }

    /**
     * @param mixed $reprographyCost
     */
    public function setReprographyCost($reprographyCost)
    {
        $this->reprographyCost = $reprographyCost;
    }

    /**
     * @return mixed
     */
    public function getReprographyCost()
    {
        return $this->reprographyCost;
    }

    /**
     * @param mixed $subscriptionRightTaking
     */
    public function setSubscriptionRightTaking($subscriptionRightTaking)
    {
        $this->subscriptionRightTaking = $subscriptionRightTaking;
    }

    /**
     * @return mixed
     */
    public function getSubscriptionRightTaking()
    {
        return $this->subscriptionRightTaking;
    }

    /**
     * @param mixed $training
     */
    public function setTraining($training)
    {
        $this->training = $training;
    }

    /**
     * @return Training
     */
    public function getTraining()
    {
        return $this->training;
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
    public function getDateBegin()
    {

        return $this->dateBegin;
    }

    /**
     * @param mixed $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
    }

    /**
     * @return mixed
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param mixed $trainers
     */
    public function setTrainers($trainers)
    {
        $this->trainers = $trainers;
    }

    /**
     * @return mixed
     */
    public function getTrainers()
    {
        return $this->trainers;
    }

    /**
     * @param $trainer
     */
    public function addTrainer($trainer)
    {
        $this->trainers->add($trainer);
    }

    /**
     * @param $trainer
     */
    public function removeTrainer($trainer)
    {
        if($this->trainers->contains($trainer)) {
            $this->trainers->removeElement($trainer);
        }
    }

    /**
     * @param $trainer
     * @return boolean
     */
    public function hasTrainer($trainer)
    {
        return $this->trainers->contains($trainer);
    }

    /**
     * @param mixed $inscriptions
     */
    public function setInscriptions($inscriptions)
    {
        $this->inscriptions = $inscriptions;
    }

    /**
     * @return Inscription[]
     */
    public function getInscriptions()
    {
        return $this->inscriptions;
    }

    /**
     * @return mixed
     */
    public function getParticipantsSummaries()
    {
        if($this->getRegistration() > Session::REGISTRATION_DEACTIVATED) {
            // if the registration is not deactivated, get info from inscriptions
            $summaries = array();
            foreach($this->getInscriptions() as $inscription) {
                if($inscription->getPresenceStatus() && $inscription->getPresenceStatus()->getStatus() == PresenceStatus::STATUS_PRESENT && $inscription->getPublicType()) {
                    $publicType = $inscription->getPublicType();
                    $disciplinary = $inscription->getDisciplinary();
                    $key = $publicType->getId().'-'.($disciplinary ? $disciplinary->getId() : 0);
                    $summary = &$summaries[$key];
                    if(!$summary) {
                        $summary = new ParticipantsSummary();
                        $summary->setPublicType($publicType);
                        $summary->setDisciplinary($disciplinary);
                        $summary->setSession($this);
                        $summaries[$key] = $summary;
                    }
                    $summary->setCount($summary->getCount()+1);
                }
            }
            return array_values($summaries);
        } else {
            // else, get info from the entity
            return $this->participantsSummaries;
        }
    }

    /**
     * @param mixed $participantsSummaries
     */
    public function setParticipantsSummaries($participantsSummaries)
    {
        foreach($participantsSummaries as $summary) {
            $summary->setSession($this);
        }
        $this->participantsSummaries = $participantsSummaries;
    }

    /**
     * @param $participantsSummarie
     */
    public function addParticipantsSummary($participantSummary)
    {
        foreach ($this->participantsSummaries as $participiantsSummary) {
            if ($participiantsSummary->getPublicType() == $participantSummary->getPublicType() &&
                $participiantsSummary->getSession() == $participantSummary->getSession()) {
                $participiantsSummary->setCount($participiantsSummary->getCount() + $participantSummary->getCount());
                return;
            }
        }
        $participantSummary->setSession($this);
        $this->participantsSummaries->add($participantSummary);
    }

    /**
     * @param $participantsSummarie
     */
    public function removeParticipantsSummary($participantsSummarie)
    {
        if($this->participantsSummaries->contains($participantsSummarie)) {
            $this->participantsSummaries->removeElement($participantsSummarie);
        }
    }

    /**
     * The session is registrable
     */
    public function isRegistrable()
    {
        $now = new \DateTime();

        // check date
        if($this->getDateBegin() <= $now) {
            return false;
        }

        // check status
        if($this->getRegistration() < self::REGISTRATION_PRIVATE) {
            return false;
        }

        // check limit registration date
        if($this->getLimitRegistrationDate() && $this->getLimitRegistrationDate() < $now) {
            return false;
        }

        // check max participants
        if($this->getAvailablePlaces() <= 0) {
            return false;
        }

        // else
        return true;
    }

    /**
     * hack : for serialization
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session", "training", "api.training"})
     */
    public function registrable()
    {
        return $this->isRegistrable();
    }

    /**
     * @param String $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return String
     */
    public function getComments()
    {
        return $this->comments;
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
    public function getSchedule()
    {
        return $this->schedule;
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
     * Return available places
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"api.training"})
     */
    public function getAvailablePlaces() {
        return $this->getMaximumNumberOfRegistrations() - $this->getNumberOfAcceptedRegistrations();
    }

    /**
     * Update the limit registration date
     *
     * @ORM\PrePersist
     */
    public function updateLimitRegistrationDate()
    {
        // if the limit registration date is not set,
        // set it to the day before date begin
        if(!$this->getLimitRegistrationDate()) {
            $date = clone $this->getDateBegin();
            $date->modify('-1 day');
            $this->setLimitRegistrationDate($date);
        }
    }

    /**
     * HumanReadablePropertyAccessor helper function : allows to get a single string containing all trainers
     * @return String
     */
    public function getTrainersListString()
    {
        if ( empty($this->trainers) ) return "";
        else {
            $names = array();
            foreach ($this->trainers as $trainer) {
                $names[] = $trainer->getFullName();
            }

            return implode (", ",$names);
        }
    }

}
