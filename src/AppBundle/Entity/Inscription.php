<?php

namespace AppBundle\Entity;

use Elastica\Filter\Ids;
use Elastica\Filter\Term;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\BoolNot;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Term\Priority;
use AppBundle\Entity\Trainee\Trainee;
use AppBundle\Form\Type\InscriptionType;
use AppBundle\Entity\Term\Training\Theme;
use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Entity\Evaluation\Evaluation;
use AppBundle\Entity\Term\Trainee\Typology;
use AppBundle\Entity\Trainee\EmployeeTrait;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\AbstractInscription;
use Sygefor\Bundle\CoreBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\CoreBundle\Utils\Search\SearchService;

/**
 * @ORM\Table(name="inscription")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Inscription extends AbstractInscription
{
    use CoordinatesTrait;
    use ProfessionalSituationTrait;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $inscriptionStatusUpdatedAt;

    /**
     * @var string
     * @ORM\Column(name="motivation", type="text", nullable=true)
     * @Serializer\Groups({"Default", "api"})
     */
    protected $motivation;

    /**
     * @var \DateTime
     * @ORM\Column(name="lastEmailDate", type="datetime")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $lastEmailDate;

    /**
     * @var Evaluation
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Evaluation\Evaluation", mappedBy="inscription", cascade={"persist", "remove"})
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $evaluation;

    public function __toString()
    {
        return strval($this->getId());
    }

    /**
     * @return \DateTime
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"api"})
     */
    public function getDate()
    {
        return $this->getCreatedAt();
    }

    /**
     * Save update date for property inscription status.
     *
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setInscriptionStatusUpdatedAtLifecycle(LifecycleEventArgs $eventArgs)
    {
        $uow = $eventArgs->getEntityManager()->getUnitOfWork();
        $changeset = $uow->getEntityChangeSet($this);
        if (isset($changeset['inscriptionStatus'])) {
            $this->setInscriptionStatusUpdatedAt((new \DateTime('now', new \DateTimeZone('Europe/Paris'))));
        }
    }

    /**
     * @return \DateTime
     */
    public function getInscriptionStatusUpdatedAt()
    {
        return $this->inscriptionStatusUpdatedAt;
    }

    /**
     * @param \DateTime $inscriptionStatusUpdatedAt
     */
    public function setInscriptionStatusUpdatedAt($inscriptionStatusUpdatedAt)
    {
        $this->inscriptionStatusUpdatedAt = $inscriptionStatusUpdatedAt;
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
    public function getLastEmailDate()
    {
        return $this->lastEmailDate;
    }

    /**
     * @param mixed $lastEmailDate
     */
    public function setLastEmailDate($lastEmailDate)
    {
        $this->lastEmailDate = $lastEmailDate;
    }

    /**
     * @return Evaluation
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    /**
     * @param Evaluation $evaluation
     */
    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * @return bool
     */
    public function hasEvaluated()
    {
        return $this->evaluation !== null;
    }

    /**
     * @return int
     */
    public function getPersonHours()
    {
        if ($this->presenceStatus && $this->presenceStatus->getStatus() === PresenceStatus::STATUS_PRESENT) {
            return $this->getSession()->getHourNumber();
        }

        return 0;
    }

    /**
     * @Serializer\VirtualProperty
     *
     * @param $front_root_url
     *
     * @return string
     * @return string
     */
    public function getEvaluationUrl($front_root_url = 'https://sygefor.com')
    {
        return $front_root_url.'/account/attendance/'.$this->getId().'/evaluation';
    }

    public static function getFormType()
    {
        return InscriptionType::class;
    }

    /**
     * @param SearchService $search
     * @param Trainee $trainee
     * @param Theme $theme
     * @param Inscription|null $inscription
     *
     * @return array
     */
    public static function getTraineeThemeInscription(SearchService $search, Trainee $trainee, Theme $theme, Inscription $inscription = null)
    {
        $filters = new BoolAnd();
        $filters->addFilter((new Term(array('inscription.trainee.id' => $trainee->getId()))));
        $filters->addFilter((new Term(array('inscription.session.training.theme.id' => $theme->getId()))));
        $filters->addFilter((new Term(array('inscription.presenceStatus.status' => PresenceStatus::STATUS_PRESENT))));
        if ($inscription) {
            $filters->addFilter((new BoolNot((new Ids('inscription', array($inscription->getId()))))));
        }

        $search->addFilter('filters', $filters);
        $search->addSort('session.dateBegin', 'desc');

        return $search->search();
    }
}
