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
}
