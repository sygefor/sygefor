<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 24/08/2015
 * Time: 14:34
 */

namespace Sygefor\Bundle\CoreBundle\Entity;

use Sygefor\Bundle\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use JMS\Serializer\Annotation as Serializer;

/**
 * Email
 *
 * @ORM\Table(name="email")
 * @ORM\Entity
 */
class Email
{
    /**
     * @var integer id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User $userFrom
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\User\User")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Serializer\Groups({"user"})
     */
    protected $userFrom;

    /**
     * @var string $emailFrom
     * @ORM\Column(name="emailFrom", type="string", length=128, nullable=true)
     */
    protected $emailFrom;

    /**
     * @var $trainee
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    protected $trainee;

    /**
     * @var $trainer
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainerBundle\Entity\AbstractTrainer")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    protected $trainer;

    /**
     * @var $session
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $session;

    /**
     * @var \DateTime $sendAt
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $sendAt;

    /**
     * @var string $subject
     * @ORM\Column(name="subject", type="string", length=512, nullable=true)
     */
    protected $subject;

    /**
     * @var string $body
     * @ORM\Column(name="body", type="text", nullable=true)
     */
    protected $body;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUserFrom()
    {
        return $this->userFrom;
    }

    /**
     * @param User $userFrom
     */
    public function setUserFrom($userFrom)
    {
        $this->userFrom = $userFrom;
    }

    /**
     * @return string
     */
    public function getEmailFrom()
    {
        return $this->emailFrom;
    }

    /**
     * @param string $emailFrom
     */
    public function setEmailFrom($emailFrom)
    {
        $this->emailFrom = $emailFrom;
    }

    /**
     * @return mixed
     */
    public function getTrainee()
    {
        return $this->trainee;
    }

    /**
     * @param mixed $trainee
     */
    public function setTrainee($trainee)
    {
        $this->trainee = $trainee;
    }

    /**
     * @return mixed
     */
    public function getTrainer()
    {
        return $this->trainer;
    }

    /**
     * @param mixed $trainer
     */
    public function setTrainer($trainer)
    {
        $this->trainer = $trainer;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param mixed $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * @return string
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * @param string $sendAt
     */
    public function setSendAt($sendAt)
    {
        $this->sendAt = $sendAt;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
}