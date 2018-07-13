<?php

namespace AppBundle\Entity\Training;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use AppBundle\Form\Type\Training\MeetingType;

/**
 * @ORM\Entity
 * @ORM\Table(name="meeting")
 * traduction: Rencontre scientifique
 */
class Meeting extends SingleSessionTraining
{
    /**
     * @ORM\Column(name="national", type="boolean")
     *
     * @var bool
     * @Serializer\Groups({"training", "api"})
     */
    protected $national;

    /**
     * @return string
     */
    static public function getType()
    {
        return 'meeting';
    }

    /**
     * @return string
     */
    static public function getTypeLabel()
    {
        return 'Rencontre scientifique';
    }

    /**
     * @return string
     */
    static public function getFormType()
    {
        return MeetingType::class;
    }

    /**
     * @param bool $national
     */
    public function setNational($national)
    {
        $this->national = $national;
    }

    /**
     * @return bool
     */
    public function getNational()
    {
        return $this->national;
    }
}
