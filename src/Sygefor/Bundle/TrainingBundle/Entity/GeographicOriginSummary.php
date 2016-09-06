<?php

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublicType;
use Sygefor\Bundle\TrainingBundle\Entity\Term\GeographicOrigin;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use JMS\Serializer\Annotation as Serializer;

/**
 * Geographic origin summary for a session
 *
 */
class GeographicOriginSummary
{
    /**
     * @var Session
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
    protected $publicCategory;

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
        return $this->session->getId().'-'.$this->geographicOrigin->getId().'-'.$this->publicCategory->getId();
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
    public function getPublicCategory()
    {
        return $this->publicCategory;
    }

    /**
     * @param PublicType $publicCategory
     */
    public function setPublicCategory($publicCategory)
    {
        $this->publicCategory = $publicCategory;
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
}
