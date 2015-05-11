<?php
namespace Sygefor\Bundle\TaxonomyBundle\Entity;

use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class AbstractTerm
 * @package Sygefor\Bundle\TaxonomyBundle\Entity
 * @ORM\MappedSuperclass()
 */
abstract class AbstractTerm implements NationalVocabularyInterface
{
    use SortableTrait;

    /**
     * @var string
     */
    protected $vocabularyId;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     * @Serializer\Groups({"Default", "api"})
     */
    private $name;

    /**
     * @var boolean
     * @ORM\Column(name="private", type="boolean")
     * @Serializer\Exclude
     */
    private $private = false;

    /**
     * @return mixed
     */
    abstract public function getVocabularyName();

    /**
     * @return boolean
     */
    public function isNational()
    {
        return true;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * @param mixed $private
     */
    public function setPrivate($private)
    {
        $this->private = $private;
    }

    /**
     * @return mixed
     */
    public function getVocabularyId() {
        return $this->vocabularyId;
    }

    /**
     * @param string $id
     */
    public function setVocabularyId($id) {
        $this->vocabularyId = $id;
    }

    /**
     * @return mixed
     * This static method is used to set a specific order field
     * when fetch terms
     */
    public static function orderBy()
    {
        return  method_exists(__CLASS__, 'getPosition') ? 'position' : 'name';
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getName();
    }

}
