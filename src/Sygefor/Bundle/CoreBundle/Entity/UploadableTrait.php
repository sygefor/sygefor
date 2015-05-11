<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 10/07/14
 * Time: 14:45
 */

namespace Sygefor\Bundle\CoreBundle\Entity;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\ExecutionContextInterface;


/**
 * Class UploadableTrait
 * @package Sygefor\Bundle\CoreBundle\Entity
 * @ORM\HasLifecycleCallbacks
 */
trait UploadableTrait
{
    /**
     * @ORM\Column(name="file_path", type="string", nullable=false)
     * @var string $filePath
     */
    private $filePath;

    /**
     * @ORM\Column(name="file_name", type="string", nullable=false)
     * @var string $fileName
     */
    protected $fileName;

    /**
     * @var File $file
     */
    protected $file;

    /**
     * used to force file update when changing file.
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $uploaded;

    /**
     * @var
     */
    static protected $maxFileSize = 50000000;

    /**
     * @param String $filePath
     */
    public function setFilePath($file)
    {
        $this->filePath = $file;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        if ($this->filePath !== null) {
            $this->file = new File($this->getTemplatesRootDir().'/'.$this->filePath);
        }
        return $this->file;
    }

    /**
     * @return String
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param String $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param File $file
     */
    public function setFile(File $file = null, $name = null)
    {
        if (!empty($file)) {
            $this->uploaded = new \DateTime();
            $this->file = $file;
            if ($this->file instanceof UploadedFile) {
                $this->filePath = sha1(uniqid(mt_rand(), true)).'.'.$this->file->guessClientExtension();
                $this->fileName = $this->file->getClientOriginalName();
            } else {
                $this->filePath = $file->getFileInfo()->getFilename();
                $this->fileName = ($name) ? $name : $file->getFileInfo()->getFilename();
            }
        }
    }

    /**
     * @ORM\PrePersist()
     */
    public function preUpload()
    {
        if (null !== $this->file && ($this->file instanceof UploadedFile) ) {
            // nom unique du fichier.
            $this->filePath = sha1(uniqid(mt_rand(), true)).'.'.$this->file->guessClientExtension();
            $this->fileName = $this->file->getClientOriginalName();
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     * @ORM\PreUpdate()
     */
    public function preUpdateUpload(PreUpdateEventArgs $args)
    {
        //a new file is set : we delete the old one
        if ($args->hasChangedField('uploaded')) {//new uploaded file : old one is deleted
            unlink($this->getTemplatesRootDir().'/'.$args->getOldValue('filePath'));
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload($args)
    {
        if (null === $this->file) {
            return;
        }
        $this->file->move($this->getTemplatesRootDir(), $this->filePath);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    /**
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return ( null == $this->filePath ) ? null : $this->getTemplatesRootDir().'/'.$this->filePath;
    }

    /**
     * @return string
     */
    protected function getTemplatesRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return '/../../../../../app/Resources/default';
    }

    /**
     * @return mixed
     */
    public static function getMaxFileSize()
    {
        return self::$maxFileSize;
    }



    /**
     * Returns a response to send to the client if file is requested
     * @return Response
     */
    public function send()
    {
        $response = new Response();
        //return array();
        $fp = $this->getAbsolutePath();

        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $this->getFileName() . '";');
        $response->headers->set('Content-length', filesize($fp));
        $response->sendHeaders();
        $response->setContent(readfile($fp));

        return $response;

    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validateFileSize(ExecutionContextInterface $context){
        if ($this->file->getSize() > $this->maxFileSize){
            $context->addViolationAt('file',"La taille du fichier dépasse la limite autorisée",array(),null);
        }
    }
}
