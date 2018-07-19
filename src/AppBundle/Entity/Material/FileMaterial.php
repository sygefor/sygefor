<?php

namespace AppBundle\Entity\Material;

use AppBundle\Form\Type\Material\FileMaterialType;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\AbstractMaterial;
use Sygefor\Bundle\CoreBundle\Entity\UploadableTrait;

/**
 * FileMaterial.
 *
 * @ORM\Entity
 * @ORM\Table(name="file_material")
 * @ORM\HasLifecycleCallbacks
 */
class FileMaterial extends AbstractMaterial
{
    use UploadableTrait;

    /**
     * Get name.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("name")
     * @Serializer\Groups({"Default", "api.attendance"})
     *
     * @return string
     */
    public function getName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'file';
    }

    /**
     * @return int file max size to 50 Mo
     */
    public static function getMaxFileSize()
    {
        return 52428800;
    }

    /**
     * @return string
     */
    public static function getFormType()
    {
        return FileMaterialType::class;
    }

    /**
     * @return string
     */
    protected function getTemplatesRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../../../../var/Material';
    }
}
