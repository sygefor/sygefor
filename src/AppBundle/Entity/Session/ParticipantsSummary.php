<?php

namespace AppBundle\Entity\Session;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Term\PublicType;
use JMS\Serializer\Annotation as Serializer;

/**
 * Participants summary for a session.
 *
 * @ORM\Table(name="participants_summary")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * traduction: session
 */
class ParticipantsSummary
{
    /**
     * @var Session
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Session", inversedBy="participantsSummary")
     * @Serializer\Exclude
     */
    protected $session;

    /**
     * @var PublicType
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Term\PublicType")
     * @Serializer\Groups({"session"})
     */
    protected $publicType;

    /**
     * @ORM\Column(name="count", type="integer", nullable=true)
     */
    protected $count;

    public function __construct()
    {
        $this->count = null;
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
