<?php
namespace Sygefor\Bundle\TaxonomyBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

trait SortableTrait
{
    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     * @Serializer\Exclude
     */
    private $position = 0;

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }
}
