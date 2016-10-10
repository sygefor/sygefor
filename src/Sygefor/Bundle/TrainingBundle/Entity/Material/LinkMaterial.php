<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Material;


use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * LinkMaterial.
 *
 * @ORM\Entity
 * @ORM\Table(name="link_material")
 */
class LinkMaterial extends Material
{
    /**
     * @ORM\Column(name="url", type="string", nullable=false)
     * @Assert\Url(message="Url non valide !")
     * @Serializer\Groups({"Default", "api.attendance"})
     */
    private $url;

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $link
     */
    public function setUrl($link)
    {
        $this->url = $link;
    }

    /**
     * @return string
     */
    static public function getType()
    {
        return 'link';
    }
}
