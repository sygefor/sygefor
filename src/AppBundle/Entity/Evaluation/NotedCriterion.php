<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 12/5/17
 * Time: 4:16 PM.
 */

namespace AppBundle\Entity\Evaluation;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use AppBundle\Entity\Term\Evaluation\Criterion;

/**
 * @ORM\Table(name="evaluated_criterion")
 * @ORM\Entity
 */
class NotedCriterion
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
     * @var EvaluatedTheme
     * @ORM\ManyToOne(targetEntity="EvaluatedTheme", inversedBy="criteria")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Serializer\Exclude
     */
    protected $theme;

    /**
     * @var Criterion
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Term\Evaluation\Criterion")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $criterion;

    /**
     * @var int
     * @ORM\Column(name="note", type="integer")
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    protected $note;

	public function __construct($theme = null, $criterion = null, $note = null)
	{
		$this->theme = $theme;
		$this->criterion =$criterion;
		$this->note = $note;
	}

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return EvaluatedTheme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param EvaluatedTheme $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return Criterion
     */
    public function getCriterion()
    {
        return $this->criterion;
    }

    /**
     * @param Criterion $criterion
     */
    public function setCriterion($criterion)
    {
        $this->criterion = $criterion;
    }

    /**
     * @return int
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param int $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }
}
