<?php
namespace Sygefor\Bundle\TaxonomyBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * Trait TreeTrait
 * @package Sygefor\Bundle\TaxonomyBundle\Entity
 */
trait TreeTrait
{
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @Serializer\Exclude
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     * @Serializer\Exclude
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     * @Serializer\Exclude
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     * @Serializer\Exclude
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @Serializer\Exclude
     * This property will be mapped by the TreeTraitListener
     * _ORM\ManyToOne(targetEntity="__SELF__", inversedBy="children")
     * _ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @Serializer\Groups({"api"})
     * This property will be mapped by the TreeTraitListener
     * _ORM\OneToMany(targetEntity="__SELF__", mappedBy="parent")
     * _ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @param null $parent
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return bool
     * @todo perf
     */
    public function hasChildren()
    {
        return (bool) count($this->children);
    }

    /**
     * @return mixed
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return mixed
     */
    public function getRootEntity()
    {
        $entity = $this;
        while($entity->getParent()) {
            $entity = $entity->getParent();
        }
        return $entity;
    }

    /**
     * @return mixed
     */
    public function belongTo($entity)
    {
        if($this == $entity) {
            return true;
        }
        if($this->getParent()) {
            if($this->getParent()->belongTo($entity)) {
                return true;
            }
        }
        return false;
    }
}
