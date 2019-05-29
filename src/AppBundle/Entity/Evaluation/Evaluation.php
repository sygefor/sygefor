<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 12/5/17
 * Time: 3:48 PM.
 */

namespace AppBundle\Entity\Evaluation;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Inscription;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="evaluation")
 * @ORM\Entity
 */
class Evaluation
{
    /**
     * @var int id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @var Inscription
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Inscription", inversedBy="evaluation")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Serializer\Exclude
     */
    protected $inscription;

    /**
     * @ORM\OneToMany(targetEntity="EvaluatedTheme", mappedBy="evaluation", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $themes;

    /**
     * @var string
     * @ORM\Column(name="good_points", type="text", nullable=true)
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $goodPoints;

    /**
     * @var string
     * @ORM\Column(name="bad_points", type="text", nullable=true)
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $badPoints;

    /**
     * @var string
     * @ORM\Column(name="suggestions", type="text", nullable=true)
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $suggestions;

    public function __construct(Inscription $inscription = null)
    {
	    $this->inscription = $inscription;
        $this->themes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Inscription
     */
    public function getInscription()
    {
        return $this->inscription;
    }

    /**
     * @param Inscription $inscription
     */
    public function setInscription($inscription)
    {
        $this->inscription = $inscription;
    }

    /**
     * @return string
     */
    public function getSuggestions()
    {
        return $this->suggestions;
    }

    /**
     * @param string $suggestions
     */
    public function setSuggestions($suggestions)
    {
        $this->suggestions = $suggestions;
    }

    /**
     * @return string
     */
    public function getBadPoints()
    {
        return $this->badPoints;
    }

    /**
     * @param string $badPoints
     */
    public function setBadPoints($badPoints)
    {
        $this->badPoints = $badPoints;
    }

    /**
     * @return string
     */
    public function getGoodPoints()
    {
        return $this->goodPoints;
    }

    /**
     * @param string $goodPoints
     */
    public function setGoodPoints($goodPoints)
    {
        $this->goodPoints = $goodPoints;
    }

    /**
     * @return mixed
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * @param mixed $themes
     */
    public function setThemes($themes)
    {
        $this->themes = $themes;
    }

    /**
     * @param $theme
     */
    public function addTheme($theme)
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
        }
    }
}
