<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 12/6/17
 * Time: 11:47 AM.
 */

namespace AppBundle\Entity\Evaluation;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Term\Evaluation\Theme;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="evaluated_theme")
 * @ORM\Entity
 */
class EvaluatedTheme
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
     * @var Evaluation
     * @ORM\ManyToOne(targetEntity="Evaluation", inversedBy="themes")
     * @Serializer\Exclude
     */
    protected $evaluation;

    /**
     * @var Theme
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Term\Evaluation\Theme")
     * @Serializer\Exclude
     */
    protected $theme;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="NotedCriterion", mappedBy="theme", cascade={"persist", "remove"})
     * @Serializer\Groups({"training", "api.attendance"})
     */
    protected $criteria;

    /**
     * @var string
     * @ORM\Column(name="comments", type="text", nullable=true)
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $comments;

    public function __construct($evaluation = null, $theme = null, $criteria = null, $comments = null)
    {
    	$this->evaluation = $evaluation;
    	$this->theme = $theme;
        $this->criteria = ($criteria ?: new ArrayCollection());
	    $this->comments = $comments;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Evaluation
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    /**
     * @param Evaluation $evaluation
     */
    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param Theme $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return ArrayCollection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param ArrayCollection $criteria
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @param $criterion
     */
    public function addCriterion($criterion)
    {
        if (!$this->criteria->contains($criterion)) {
            $this->criteria->add($criterion);
        }
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }
}
