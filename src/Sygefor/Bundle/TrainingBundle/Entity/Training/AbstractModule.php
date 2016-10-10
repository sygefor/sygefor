<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 6/7/16
 * Time: 10:34 AM.
 */
namespace Sygefor\Bundle\TrainingBundle\Entity\Training;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Sygefor\Bundle\TrainingBundle\Form\BaseModuleType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AbstractModule.
 *
 * @ORM\Table(name="module")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
abstract class AbstractModule
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Vous devez renseigner un nom pour ce module.")
     * @ORM\Column(name="name", type="string", length=256)
     * @Serializer\Groups({"session", "training", "api.training", "api.session"})
     */
    protected $name;

    /*
     * @var AbstractTraining
     * @ORM\ManyToOne(targetEntity="SygeforTrainingBundle:Training\AbstractTraining", inversedBy="modules")
     * @ORM\JoinColumn(name="training_id", referencedColumnName="id")
     * @Assert\NotNull(message="Vous devez sélectionner une formation.")
     * @Serializer\Groups({"session", "training", "api.training", "api.session"})
     */
    protected $training;

    /**
     * @ORM\Column(name="mandatory", type="boolean")
     *
     * @var bool
     * @Serializer\Groups({"session", "training", "api.training", "api.session"})
     */
    protected $mandatory;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession", mappedBy="module", fetch="EXTRA_LAZY")
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return AbstractTraining
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * @param AbstractTraining $training
     */
    public function setTraining($training)
    {
        $this->training = $training;
    }

    /**
     * @return bool
     */
    public function isMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @param bool $mandatory
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
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
     * @param AbstractSession $session
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
     * @param AbstractSession $session
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

    function __toString()
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    static public function getFormType()
    {
        return BaseModuleType::class;
    }

    /**
     * @return string
     */
    static public function getType()
    {
        return 'module';
    }
}
