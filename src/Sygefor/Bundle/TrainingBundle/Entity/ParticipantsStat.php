<?php
/**
 * Created by PhpStorm.
 * User: Blaise
 * Date: 04/03/2016
 * Time: 16:59
 */

namespace Sygefor\Bundle\TrainingBundle\Entity;


use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublicType;
use Sygefor\Bundle\TrainingBundle\Entity\Term\GeographicOrigin;

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
     * @var Disciplinary
     */
    protected $disciplinary;

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
     * hack for elastica bundle
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
     * @param Disciplinary $disciplinary
     */
    public function setDisciplinary($disciplinary)
    {
        $this->disciplinary = $disciplinary;
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

    /**
     *
     */
    function incrementCount() {
        $this->count++;
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
}