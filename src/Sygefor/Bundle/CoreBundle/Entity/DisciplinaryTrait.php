<?php
namespace Sygefor\Bundle\CoreBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait DisciplinaryTrait
 * @package Sygefor\Bundle\CoreBundle\Entity
 */
trait DisciplinaryTrait
{
    /**
     * Assert : @see additionalAsserts
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary")
     * @Serializer\Exclude //Serializer exclude is important, we use virtual properties instead (see below)
     */
    protected $disciplinary;

    /**
     * @param mixed $disciplinary
     */
    public function setDisciplinaryDomain($disciplinary)
    {
        if(!$disciplinary || $disciplinary->getLvl() == 0) {
            if($this->getDisciplinary() && $this->getDisciplinary()->getRoot() == $disciplinary->getId()) {
                // if deepest property has been set with this root, dont update the property
                return;
            }
            $this->disciplinary = $disciplinary;
        }
    }

    /**
     * @return PublicType
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"trainee", "training", "inscription", "api.profile"})
     */
    public function getDisciplinaryDomain(){
        if($this->disciplinary) {
            return $this->disciplinary->getRootEntity();
        }
        return null;
    }

    /**
     * @param mixed $disciplinary
     */
    public function setDisciplinary($disciplinary)
    {
        if(!$disciplinary || $disciplinary->getLvl() > 0) {
            $this->disciplinary = $disciplinary;
        }
    }

    /**
     * @return Disciplinary
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"trainee", "training", "inscription", "api.profile"})
     */
    public function getDisciplinary()
    {
        if($this->disciplinary && $this->disciplinary->getLvl() > 0) {
            return  $this->disciplinary;
        }
        return null;
    }
}
