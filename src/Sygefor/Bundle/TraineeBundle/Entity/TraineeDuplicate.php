<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 31/03/2015
 * Time: 09:32
 */

namespace Sygefor\Bundle\TraineeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\CoordinatesTrait;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * TraineeDuplicate
 * @ORM\Table(name="trainee_duplicate")
 * @ORM\Entity()
 */
class TraineeDuplicate
{
    /**
     * @var integer id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     *@ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Trainee")
     */
    protected $traineeSource;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Trainee")
     */
    protected $traineeTarget;

    /**
     * @var boolean
     * @ORM\Column(name="ignored", type="boolean")
     */
    protected $ignored;

    /**
     * @ORM\Column(type="string", length=128)
     * @var string
     */
    protected $type;

    public function clear()
    {
        $this->traineeTargets->clear();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Trainee
     */
    public function getTraineeSource()
    {
        return $this->traineeSource;
    }

    /**
     * @param Trainee $traineeSource
     */
    public function setTraineeSource(Trainee $traineeSource)
    {
        $this->traineeSource = $traineeSource;
    }

    /**
     * @return Trainee
     */
    public function getTraineeTarget()
    {
        return $this->traineeTarget;
    }

    /**
     * @param Trainee $traineeTarget
     */
    public function setTraineeTarget(Trainee $traineeTarget)
    {
        $this->traineeTarget = $traineeTarget;
    }

    /**
     * @return boolean
     */
    public function isIgnored()
    {
        return $this->ignored;
    }

    /**
     * @param boolean $ignored
     */
    public function setIgnored($ignored)
    {
        $this->ignored = $ignored;
        $this->getTraineeSource()->updateTimestamps();
        $this->getTraineeTarget()->updateTimestamps();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}