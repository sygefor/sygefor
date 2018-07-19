<?php

namespace AppBundle\Entity\Trainee;

use JMS\Serializer\Annotation as Serializer;
use AppBundle\Entity\Term\Trainee\Disciplinary;
use AppBundle\Entity\Term\PublicType;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait DisciplinaryTrait.
 */
trait DisciplinaryTrait
{
    /**
     * Assert : @see additionalAsserts.
     *
     * @var Disciplinary
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Term\Trainee\Disciplinary")
     * @Serializer\Exclude //Serializer exclude is important, we use virtual properties instead (see below)
     */
    protected $disciplinary;

    /**
     * @param DisciplinaryTrait $entity
     * @param bool              $force
     *
     * @throws \Throwable
     * @throws \TypeError
     */
    public function copyDisciplinary($entity, $force = true)
    {
        $propertyAccessor = new PropertyAccessor();
        foreach (array('disciplinary') as $property) {
            $thisValue = $propertyAccessor->getValue($this, $property);
            if ($force || !$thisValue) {
                $propertyAccessor->setValue($this, $property, $propertyAccessor->getValue($entity, $property));
            }
        }
    }

    /**
     * @param Disciplinary $disciplinary
     */
    public function setDisciplinaryDomain($disciplinary)
    {
        if (!$disciplinary || $disciplinary->getLvl() === 0) {
            if ($this->getDisciplinary() && $this->getDisciplinary()->getRoot() === $disciplinary->getId()) {
                // if deepest property has been set with this root, dont update the property
                return;
            }
            $this->disciplinary = $disciplinary;
        }
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"trainee", "training", "inscription", "api.profile"})
     *
     * @return Disciplinary
     */
    public function getDisciplinaryDomain()
    {
        if ($this->disciplinary) {
            return $this->disciplinary->getRootEntity();
        }

        return null;
    }

    /**
     * @param Disciplinary $disciplinary
     */
    public function setDisciplinary($disciplinary)
    {
        if (!$disciplinary || $disciplinary->getLvl() > 0) {
            $this->disciplinary = $disciplinary;
        }
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"trainee", "training", "inscription", "api.profile"})
     *
     * @return Disciplinary
     */
    public function getDisciplinary()
    {
        if ($this->disciplinary && $this->disciplinary->getLvl() > 0) {
            return $this->disciplinary;
        }

        return null;
    }
}
