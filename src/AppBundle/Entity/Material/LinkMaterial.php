<?php

namespace AppBundle\Entity\Material;

use AppBundle\Form\Type\Material\LinkMaterialType;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\AbstractMaterial;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * LinkMaterial.
 *
 * @ORM\Entity
 * @ORM\Table(name="link_material")
 */
class LinkMaterial extends AbstractMaterial
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
    public static function getFormType()
    {
        return LinkMaterialType::class;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'link';
    }
}
