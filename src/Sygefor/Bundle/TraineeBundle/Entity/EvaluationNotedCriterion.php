<?php
namespace Sygefor\Bundle\TraineeBundle\Entity;

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
 * EvaluationScoredCriteria
 *
 * @ORM\Table(name="evaluation__evaluation_criterion")
 * @ORM\Entity
 */
class EvaluationNotedCriterion
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Evaluation", inversedBy="criteria")
     * @ORM\JoinColumn(name="inscription_id", referencedColumnName="inscription_id")
     * @Serializer\Exclude
     */
    protected $evaluation;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Term\EvaluationCriterion")
     * @ORM\JoinColumn(name="criterion_id", referencedColumnName="id")
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
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    /**
     * @param mixed $evaluation
     */
    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
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
