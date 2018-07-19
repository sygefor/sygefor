<?php

/**
 * Created by PhpStorm.
 * User: Blaise
 * Date: 04/03/2016
 * Time: 16:59.
 */

namespace AppBundle\Entity\Session;

use AppBundle\Entity\Term\PublicType;
use JMS\Serializer\Annotation as Serializer;

class ParticipantsStat
{
    /**
     * @var Session
     * @Serializer\Exclude
     */
    protected $session;

    /**
     * @var PublicType
     */
    protected $publicType;

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

    public function incrementCount()
    {
        ++$this->count;
    }
}
