<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Session;

use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\Institution\Term\GeographicOrigin;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType;

/**
 * Geographic origin summary for a session.
 */
class GeographicOriginSummary
{
    /**
     * @var AbstractSession
     * @Serializer\Exclude
     */
    protected $session;

    /**
     * @var GeographicOrigin
     */
    protected $geographicOrigin;

    /**
     * @var PublicType
     */
    protected $publicType;

    /**
     * @var int
     */
    protected $count;

    /**
     * GeographicOriginSummary constructor.
     */
    public function __construct()
    {
        $this->count = 0;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->session->getId() . '-' . $this->geographicOrigin->getId() . '-' . $this->publicType->getId();
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
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * @return GeographicOrigin
     */
    public function getGeographicOrigin()
    {
        return $this->geographicOrigin;
    }

    /**
     * @param GeographicOrigin $geographicOrigin
     */
    public function setGeographicOrigin($geographicOrigin)
    {
        $this->geographicOrigin = $geographicOrigin;
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
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    function incrementCount()
    {
        ++$this->count;
    }
}
