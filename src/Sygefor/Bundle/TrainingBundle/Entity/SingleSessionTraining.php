<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 03/06/14
 * Time: 09:42
 */

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class SingleSessionTraining
 * @package Sygefor\Bundle\TrainingBundle\Entity
 * @ORM\HasLifecycleCallbacks
 */
abstract class SingleSessionTraining extends Training
{
    /**
     * sets the session for training
     *
     * IMPORTANT : this function is not called with the custom training form due to hard workflow (impossibility to initialize empty Session with default values).
     * See \Sygefor\Bundle\TrainingBundle\Form\SingleSessionTrainingType
     *
     * @param Session $session
     * @deprecated
     */
    public function setSession(Session $session)
    {
        $this->sessions = new ArrayCollection();
        $session->setTraining($this);
        $this->sessions->add($session);
    }

    /**
     * Returns the training session if set, null otherwise
     * @return Session|null
     * @Serializer\VirtualProperty
     */
    public function getSession()
    {
        return $this->getSessions()->get(0);
    }

    /**
     * cloning magic function
     */
    public function __clone()
    {
        $this->setId(null) ;
        $this->setNumber(null) ;
        $this->setCreatedAt(new \DateTime());
        //session is copied
        if (!empty ($this->sessions)) {
            foreach ($this->sessions as $session) {
                $cloned = clone $session;
                $cloned->resetCostAndConsideration();
                $cloned->setNumberOfRegistrations(0);
                $cloned->setTraining($this);
                $this->setSession($cloned);
            }
        }

        $tmpMaterials = $this->getMaterials();
        if (!empty($tmpMaterials)) {
            foreach ($tmpMaterials as $material) {
                $newMat = clone $material;
                $this->addMaterial($newMat);
            }
        }
    }
}
