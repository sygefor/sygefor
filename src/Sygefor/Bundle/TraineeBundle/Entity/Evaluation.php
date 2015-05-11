<?php
namespace Sygefor\Bundle\TraineeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\CoordinatesTrait;
use Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use JMS\Serializer\Annotation as Serializer;

/**
 * Trainee
 *
 * @ORM\Table(name="evaluation")
 * @ORM\Entity
 */
class Evaluation
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Inscription", mappedBy="evaluation")
     * @ORM\JoinColumn(name="inscription_id", referencedColumnName="id")
     * @Serializer\Exclude
     */
    protected $inscription;

    /**
     * @ORM\Id
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\EvaluationNotedCriterion", mappedBy="evaluation", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="inscription_id", referencedColumnName="id")
     * @Serializer\Groups({"Default", "api.attendance"})
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
    function __construct(Inscription $inscription)
    {
        $this->inscription = $inscription;
        $this->criteria = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getInscription()->getId();
    }

    /**
     * @return Inscription
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
    public function addCriterion(EvaluationNotedCriterion $criterion) {
        $criterion->setEvaluation($this);
        $this->criteria->add($criterion);
    }
}
