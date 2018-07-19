<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 7/27/17
 * Time: 12:27 PM.
 */

namespace AppBundle\Entity\Session;

use AppBundle\Entity\Inscription;
use AppBundle\Entity\Training\LongTraining;
use Doctrine\Common\Collections\ArrayCollection;
use Sygefor\Bundle\CoreBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\CoreBundle\Entity\Term\InscriptionStatus;

trait SummaryTrait
{
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Session\ParticipantsSummary", mappedBy="session", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     * @Serializer\Groups({"session"})
     */
    protected $participantsSummaries;

    /**
     * @return mixed
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session", "training"})
     */
    public function getNumberOfAcceptedRegistrations()
    {
        if ($this->getRegistration() === self::REGISTRATION_DEACTIVATED) {
            return $this->numberOfRegistrations;
        }

        if (empty($this->inscriptions)) {
            return 0;
        }

        $nAccepted = 0;
        foreach ($this->inscriptions as $inscription) {
            if (in_array($inscription->getInscriptionStatus()->getMachineName(), array('accept', 'summoned'))) {
                ++$nAccepted;
            }
        }

        return $nAccepted;
    }

    /**
     * Used to index in ES and filter FO longTraining sessions with it.
     *
     * @Serializer\VirtualProperty
     *
     * @return bool
     */
    public function isFirstLongTrainingSession()
    {
        if ($this->training->getType() !== LongTraining::getType()) {
            return;
        }

        $trainingSessions = $this->training->getSessions();
        $sessions = array();
        /** @var Session $session */
        foreach ($trainingSessions as $session) {
            if ($session->getStatus() === Session::STATUS_OPEN) {
                $sessions[$session->getId()] = $session->getDateBegin();
            }
        }
        if (count($sessions) === 0) {
            return false;
        }
        asort($sessions);

        if (array_keys($sessions)[0] === $this->getId()) {
            return true;
        }

        return $this->module === null;
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
        } else {
            /** @var Inscription $inscription */
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
        $getStat = function ($publicType) use (&$stats) {
            $hash = array(
                $publicType ? $publicType->getId() : 0,
            );
            $id = implode('-', $hash);
            if (isset($stats[$id])) {
                return $stats[$id];
            }
            $stat = new ParticipantsStat();
            $stat->setSession($this);
            $stat->setPublicType($publicType);
            $stats[$id] = $stat;

            return $stat;
        };

        if ($this->getRegistration() > self::REGISTRATION_DEACTIVATED) {
            // if the registration is not deactivated, get info from inscriptions
            /** @var Inscription $inscription */
            foreach ($this->getInscriptions() as $inscription) {
                if ($inscription->getPresenceStatus() && $inscription->getPresenceStatus()->getStatus() === PresenceStatus::STATUS_PRESENT) {
                    $publicType = $inscription->getPublicType();
                    $getStat($publicType)->incrementCount();
                }
            }
        }
        // get it from participants summaries
        else {
            foreach ($this->getParticipantsSummaries() as $summary) {
                $publicType = $summary->getPublicType();
                if ($summary->getCount()) {
                    $getStat($publicType)->setCount($summary->getCount());
                }
            }
        }

        return $stats;
    }
}
