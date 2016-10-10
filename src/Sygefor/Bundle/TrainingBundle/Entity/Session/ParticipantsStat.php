<?php

/**
 * Created by PhpStorm.
 * User: Blaise
 * Date: 04/03/2016
 * Time: 16:59.
 */
namespace Sygefor\Bundle\TrainingBundle\Entity\Session;

use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\InstitutionBundle\Entity\Term\GeographicOrigin;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType;

class ParticipantsStat
{
    /**
     * @var AbstractSession
     * @Serializer\Exclude
     */
    protected $session;

    /**
     * @var PublicType
     */
    protected $publicType;

    /**
     * @var GeographicOrigin
     */
    protected $geographicOrigin;

    /**
     * @var int
     */
    protected $count;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->count = 0;
    }

    /**
     * hack for elastica bundle.
     */
    public function getId()
    {
        return 0;
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
