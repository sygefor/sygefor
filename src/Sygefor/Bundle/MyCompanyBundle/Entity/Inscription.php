<?php

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Sygefor\Bundle\InscriptionBundle\Entity\AbstractInscription;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\MyCompanyBundle\Form\InscriptionType;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\TraineeBundle\Entity\DisciplinaryTrait;

/**
 *
 * @ORM\Table(name="inscription")
 * @ORM\Entity
 */
class Inscription extends AbstractInscription
{
    use DisciplinaryTrait;

    /**
     * @var String
     * @ORM\Column(name="motivation", type="text", nullable=true)
     * @Serializer\Groups({"Default", "api"})
     */
    protected $motivation;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\MyCompanyBundle\Entity\EvaluationNotedCriterion", mappedBy="inscription", cascade={"persist", "merge", "remove"})
     * @Serializer\Groups({"training", "api.attendance"})
     */
    protected $criteria;

    /**
     * @ORM\Column(name="message", type="text", nullable=true)
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $message;

    /**
     *
     */
    function __construct()
    {
        $this->criteria = new ArrayCollection();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"api"})
     */
    public function getPrice()
    {
        return $this->isPaying ? $this->getSession()->getPrice() : 0;
    }

    /**
     * @return mixed
     */
    public function getMotivation()
    {
        return $this->motivation;
    }

    /**
     * @param mixed $motivation
     */
    public function setMotivation($motivation)
    {
        $this->motivation = $motivation;
    }

    /**
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param mixed $criteria
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Add a noted criterion
     * @param EvaluationNotedCriterion $criterion
     */
    public function addCriterion(EvaluationNotedCriterion $criterion)
    {
        $this->criteria->add($criterion);
    }

    static public function getFormType()
    {
        return InscriptionType::class;
    }

    function __toString()
    {
        return strval($this->getId());
    }
}
