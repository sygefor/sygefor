<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/14/16
 * Time: 5:33 PM
 */

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractModule;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\MyCompanyBundle\Form\ModuleType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="module")
 * @ORM\Entity
 */
class Module extends AbstractModule
{
    /**
     * @var LongTraining
     * @ORM\ManyToOne(targetEntity="LongTraining", inversedBy="modules")
     * @ORM\JoinColumn(name="training_id", referencedColumnName="id")
     * @Assert\NotNull(message="Vous devez sélectionner une formation longue.")
     * @Serializer\Groups({"session", "training", "api.training", "api.session"})
     */
    protected $training;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Session", mappedBy="module", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"dateBegin" = "DESC"})
     * @Serializer\Groups({"training", "api.training"})
     */
    protected $sessions;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }

    public function __clone()
    {
        $this->sessions = new ArrayCollection();
    }

    /**
     * @return LongTraining
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * @param LongTraining $training
     */
    public function setTraining($training)
    {
        $this->training = $training;
    }

    /**
     * @return ArrayCollection
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @param mixed $sessions
     */
    public function setSessions($sessions)
    {
        $this->sessions = $sessions;
    }

    /**
     * @param Session $session
     *
     * @return bool
     */
    public function addSession($session)
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setModule($this);

            return true;
        }

        return false;
    }

    /**
     * @param Session $session
     *
     * @return bool
     */
    public function removeSession($session)
    {
        if ($this->sessions->contains($session)) {
            $this->sessions->remove($session);
            $session->setModule(null);

            return true;
        }

        return false;
    }

    /**
     * @return string
     * @Serializer\VirtualProperty
     */
    public function getDateRange()
    {
        /** @var \DateTime $minDateBegin */
        $minDateBegin = $this->sessions->first()->getDateBegin();
        /** @var \DateTime $maxDateEnd */
        $maxDateEnd = $this->sessions->first()->getDateEnd();

        /** @var Session $session */
        foreach ($this->sessions as $session) {
            if ($session->getDateBegin() < $minDateBegin) {
                $minDateBegin = $session->getDateBegin();
            }
            if ($session->getDateEnd() > $maxDateEnd) {
                $maxDateEnd = $session->getDateEnd();
            }
        }

        if ($minDateBegin->format('dd/MM/YYYY') != $maxDateEnd->format('dd/MM/YYYY')) {
            return "Du " . $minDateBegin->format('dd/MM/YYYY') . " au " . $maxDateEnd->format('dd/MM/YYYY');
        }

        return "Le " . $minDateBegin->format('dd/MM/YYYY');
    }

    /**
     * @return mixed
     */
    static public function getFormType()
    {
        return ModuleType::class;
    }
}