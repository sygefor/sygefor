<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/14/16
 * Time: 5:33 PM
 */

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


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