<?php

namespace AppBundle\Entity\Training;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Term\Training\Tag;
use AppBundle\Entity\Term\Training\Theme;
use Sygefor\Bundle\CoreBundle\Entity\User;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Form\Type\Training\AbstractTrainingType;

/**
 * Class TrainingTrait.
 */
trait TrainingTrait
{
    /**
     * @var Theme
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Term\Training\Theme")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Vous devez renseigner une thématique.")
     * @Serializer\Groups({"training", "session", "inscription", "api"})
     */
    protected $theme;

    /**
     * @ORM\Column(name="program", type="text", nullable=true)
     *
     * @var string
     * @Serializer\Groups({"training", "api"})
     */
    protected $program;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @var string
     * @Serializer\Groups({"training", "api"})
     */
    protected $description;

    /**
     * @ORM\Column(name="teaching_methods", type="text", nullable=true)
     *
     * @var string
     * @Serializer\Groups({"training", "api"})
     */
    protected $teachingMethods;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Serializer\Groups({"training"})
     */
    protected $user;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Term\Training\Tag")
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="training_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="cascade")}
     * )
     * @Serializer\Groups({"training", "api"})
     */
    protected $tags;
    /**
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "session", "api"})
     */
    public function getSerial()
    {
        if ($this->getOrganization()) {
            $parts = array(
                $this->getOrganization()->getCode(),
                $this->getType(),
                $this->getNumber(),
            );

            return implode('-', $parts);
        }

        return '';
    }

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param Theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return string
     */
    public function getProgram()
    {
        return $this->program;
    }

    /**
     * @param string $program
     */
    public function setProgram($program)
    {
        $this->program = $program;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getTeachingMethods()
    {
        return $this->teachingMethods;
    }

    /**
     * @param string $teachingMethods
     */
    public function setTeachingMethods($teachingMethods)
    {
        $this->teachingMethods = $teachingMethods;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param ArrayCollection $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param Tag $tag
     *
     * @return bool
     */
    public function addTag($tag)
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public static function getFormType()
    {
        return AbstractTrainingType::class;
    }
}
