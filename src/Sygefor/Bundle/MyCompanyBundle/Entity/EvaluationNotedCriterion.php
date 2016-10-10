<?php

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="evaluation_noted_criterion")
 * @ORM\Entity
 */
class EvaluationNotedCriterion
{
    /**
     * @var int id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @var Inscription
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\MyCompanyBundle\Entity\Inscription", inversedBy="criteria")
     * @Serializer\Exclude
     */
    protected $inscription;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\MyCompanyBundle\Entity\Term\EvaluationCriterion")
     * @ORM\JoinColumn(name="criterion_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $criterion;

    /**
     * @ORM\Column(name="note", type="integer")
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $note;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getInscription()
    {
        return $this->inscription;
    }

    /**
     * @param mixed $inscription
     */
    public function setInscription($inscription)
    {
        $this->inscription = $inscription;
    }

    /**
     * @return mixed
     */
    public function getCriterion()
    {
        return $this->criterion;
    }

    /**
     * @param mixed $criterion
     */
    public function setCriterion($criterion)
    {
        $this->criterion = $criterion;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }
}
