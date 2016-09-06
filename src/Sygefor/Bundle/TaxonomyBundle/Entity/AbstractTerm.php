<?php
namespace Sygefor\Bundle\TaxonomyBundle\Entity;

use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
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
abstract class AbstractTerm implements VocabularyInterface
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
     * @var Organization $organization
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    protected $organization;

    /**
     * @var string $machineName
     * @ORM\Column(name="machine_name", type="string", length=255, nullable=true)
     */
    protected $machineName;

    /**
     * @return mixed
     */
    abstract public function getVocabularyName();

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
     * @param Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * api helper
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "api"})
     * @return integer
     */
    public function getOrganizationId()
    {
        return $this->getOrganization() ? $this->getOrganization()->getId() : null;
    }

    /**
     * @return string|null
     */
    public function getMachineName()
    {
        return $this->machineName;
    }

    /**
     * @param string
     */
    public function setMachineName($machineName)
    {
        $this->machineName = $machineName;
    }

    /**
     * If term is used for internal system processes
     * @return bool
     */
    public function isLocked($machineName = null)
    {
        return !empty($this->machineName);
    }

    /**
     * Check machine name match
     * @return bool
     */
    public function isMachineName($machineName)
    {
        return $this->machineName === $machineName;
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
