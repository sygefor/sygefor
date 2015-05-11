<?php

namespace Sygefor\Bundle\TrainingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\UploadableTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;


/**
 * FileMaterial
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class FileMaterial extends Material
{
    /**
     *
     */
    use UploadableTrait;

    /**
     * Get name
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("name")
     * @Serializer\Groups({"Default", "api.attendance"})
     * @return string
     */
    public function getName()
    {
        return $this->fileName;
    }

    /**
     *
     */
    public function __clone()
    {
        $file = $this->getFile();
        if (!empty($file)) {
            $this->id = null ;
            $fs = new Filesystem();
            $tmpFileName = sha1(uniqid(mt_rand(), true)).'.'.$file->getFileInfo()->getExtension();
            $fs->copy($this->getTemplatesRootDir().'/'.$this->filePath, $this->getTemplatesRootDir().'/'.$tmpFileName);
            //$name = ;
            $this->setFile( new File($this->getTemplatesRootDir().'/'.$tmpFileName), $this->getFileName());
        }
    }

    /**
     * @return string
     */
    protected function getTemplatesRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../../../../../app/Resources/Material';
    }

    /**
     * @return string
     */
    static public function getType()
    {
        return 'file';
    }

}
