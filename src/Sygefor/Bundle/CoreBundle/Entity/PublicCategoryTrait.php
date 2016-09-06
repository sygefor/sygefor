<?php
namespace Sygefor\Bundle\CoreBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublicType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait PublicCategoryTrait
 * @package Sygefor\Bundle\CoreBundle\Entity
 */
trait PublicCategoryTrait
{
    /**
     * @var PublicType
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Term\PublicType")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Exclude //Serializer exclude is important, we use virtual properties instead (see below)
     */
    protected $publicType;

    /**
     * @param mixed $publicType
     */
    public function setPublicType($publicType)
    {
        $this->publicType = $publicType;
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
    public function setPublicCategory( $publicType)
    {
        if(!$publicType || $publicType->getLvl() == 0) {
            if($this->getProfessionalSituation() && $this->getProfessionalSituation()->getRoot() == $publicType->getId()) {
                // if deepest property has been set with this root, dont update the property
                return;
            }
            $this->publicType = $publicType;
        }
    }

    /**
     * @return PublicType
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"trainee", "inscription", "session", "api.profile"})
     */
    public function getPublicCategory()
    {
        if($this->publicType) {
            return $this->publicType->getRootEntity();
        }
        return null;
    }

    /**
     * @param PublicType $publicType
     */
    public function setProfessionalSituation($publicType)
    {
        if(!$publicType || $publicType->getLvl() > 0) {
            $this->publicType = $publicType;
        }
    }

    /**
     * @return PublicType
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"trainee", "inscription", "api.profile"})
     */
    public function getProfessionalSituation()
    {
        if($this->publicType && $this->publicType->getLvl() > 0) {
            return  $this->publicType;
        }
        return null;
    }

    /**
     * Return true if the entity belong to the given public type
     *
     * @param $id
     * @param null $publicType
     * @return bool
     */
    function belongToPublicType($id, $publicType = null) {
        $publicType = $publicType ? $publicType : $this->getPublicType();
        if(!$publicType) {
            return false;
        }
        if($publicType->getId() == $id) {
            return true;
        }
        if($publicType->getParent()) {
            if($this->belongToPublicType($id, $publicType->getParent())) {
                return true;
            }
        }
        return false;
    }
}
