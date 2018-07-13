<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 7/27/17
 * Time: 12:26 PM.
 */

namespace AppBundle\Entity\Session;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

trait BalanceTrait
{
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $reprographyCost;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $otherCost;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $subscriptionRightTaking;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Serializer\Groups({"session"})
     */
    protected $otherTaking;

    public function resetCostAndConsideration()
    {
        $this->setReprographyCost(null);
        $this->setOtherCost(null);
        $this->setSubscriptionRightTaking(null);
        $this->setOtherTaking(null);
    }

    /**
     * @return mixed
     */
    public function getReprographyCost()
    {
        return $this->reprographyCost;
    }

    /**
     * @param mixed $reprographyCost
     */
    public function setReprographyCost($reprographyCost)
    {
        $this->reprographyCost = $reprographyCost;
    }

    /**
     * @return mixed
     */
    public function getOtherCost()
    {
        return $this->otherCost;
    }

    /**
     * @param mixed $otherCost
     */
    public function setOtherCost($otherCost)
    {
        $this->otherCost = $otherCost;
    }

    /**
     * @return float
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session"})
     */
    public function getTotalCost()
    {
        return $this->reprographyCost + $this->otherCost;
    }

    /**
     * @return mixed
     */
    public function getSubscriptionRightTaking()
    {
        return $this->subscriptionRightTaking;
    }

    /**
     * @param mixed $subscriptionRightTaking
     */
    public function setSubscriptionRightTaking($subscriptionRightTaking)
    {
        $this->subscriptionRightTaking = $subscriptionRightTaking;
    }

    /**
     * @return mixed
     */
    public function getOtherTaking()
    {
        return $this->otherTaking;
    }

    /**
     * @param mixed $otherTaking
     */
    public function setOtherTaking($otherTaking)
    {
        $this->otherTaking = $otherTaking;
    }

    /**
     * @return float
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"session"})
     */
    public function getTotalTaking()
    {
        return $this->subscriptionRightTaking + $this->otherTaking;
    }
}
