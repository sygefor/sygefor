<?php

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublicType;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use JMS\Serializer\Annotation as Serializer;

/**
 * Participants summary for a session
 *
 * @ORM\Table(name="participants_summary")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * traduction: session
 *
 */
class ParticipantsSummary
{
    /**
     * @var Session
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session", inversedBy="participantsSummary")
     * @Serializer\Exclude
     */
    protected $session;

    /**
     * @var PublicType
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Term\PublicType")
     * @Serializer\Groups({"session"})
     */
    protected $publicType;

    /**
     * @var Disciplinary
     * @Serializer\Exclude
     */
    protected $disciplinary;

    /**
     * @ORM\Column(name="count", type="integer", nullable=true)
     */
    protected $count;

    /**
     *
     */
    function __construct() {
        $this->count = NULL;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param mixed $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * @return PublicType
     */
    public function getPublicType()
    {
        return $this->publicType;
    }

    /**
     * @param PublicType $publicType
     */
    public function setPublicType($publicType)
    {
        $this->publicType = $publicType;
    }

    /**
     * @return PublicType
     */
    public function getPublicCategory()
    {
        if($this->publicType) {
            return $this->publicType->getRootEntity();
        }
        return null;
    }

    /**
     * For activity report
     * return the legacy public category (search over parents)
     * @return PublicType
     */
    public function getLegacyPublicCategory()
    {
        if($this->publicType) {
            $entity = $this->publicType;
            while ($entity) {
                if ($entity->getLegacyPublicType()) {
                    return $entity->getLegacyPublicType();
                }
                if ($entity->getParent()) {
                    $entity = $entity->getParent();
                } else {
                    return $entity;
                }
            }
        }
        return null;
    }

    /**
     * @return PublicType
     */
    public function getProfessionalSituation()
    {
        if($this->publicType && $this->publicType->getLvl() > 0) {
            return  $this->publicType;
        }
        return null;
    }

    /**
     * @param mixed $disciplinary
     */
    public function setDisciplinary($disciplinary)
    {
        $this->disciplinary = $disciplinary;
    }

    /**
     * @return mixed
     */
    public function getDisciplinary()
    {
        if($this->disciplinary) {
            return $this->disciplinary;
        }
        // if the training has disciplinary
        $training = $this->getSession()->getTraining();
        if(method_exists($training, "getDisciplinary")) {
            return $training->getDisciplinary();
        }
        return null;
    }

    /**
     * For activity report
     * return the disciplinary domain
     * @return Disciplinary
     */
    public function getDisciplinaryDomain()
    {
        // if there is a disciplinary attached to the summary
        if($this->disciplinary) {
            return $this->disciplinary->getRootEntity();
        }
        // if the training has disciplinary
        $training = $this->getSession()->getTraining();
        if(method_exists($training, "getDisciplinaryDomain")) {
            return $training->getDisciplinaryDomain();
        }
        return null;
    }

    /**
     * hack for elasticsearch
     */
    public function getId()
    {
        return 0;
    }


}
