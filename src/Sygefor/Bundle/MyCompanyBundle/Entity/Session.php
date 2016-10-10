<?php

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\MyCompanyBundle\Form\SessionType;
use Sygefor\Bundle\MyCompanyBundle\Entity\Module;

/**
 *
 * @ORM\Table(name="session")
 * @ORM\Entity
 */
class Session extends AbstractSession
{
    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Serializer\Groups({"session", "inscription", "api"})
     */
    protected $name;

    /**
     * @var Module
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\MyCompanyBundle\Entity\Module", inversedBy="sessions")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id", nullable=true)
     * @Serializer\Groups({"session", "api.training", "api.inscription"})
     */
    protected $module;

    /**
     * Used for session creation form only.
     *
     * @var Module
     */
    protected $newModule;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Serializer\Groups({"session", "inscription", "api"})
     */
    protected $price;

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
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param Module $module
     */
    public function setModule($module)
    {
        $this->module = $module;
        if ($module) {
            $this->training->addModule($module);
        }
    }

    /**
     * @return Module
     */
    public function getNewModule()
    {
        return $this->newModule;
    }

    /**
     * @param Module $newModule
     */
    public function setNewModule($newModule)
    {
        $this->newModule = $newModule;
    }

    /**
     * @return ArrayCollection
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param ArrayCollection $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @Serializer\VirtualProperty
     *
     * @param $front_root_url
     * @param $apiSerialization
     *
     * @return string
     * @return string
     */
    public function getFrontUrl($front_root_url = 'http://sygefor.dev', $apiSerialization = false)
    {
        return parent::getFrontUrl($front_root_url, $apiSerialization);
    }

    public static function getFormType()
    {
        return SessionType::class;
    }
}
