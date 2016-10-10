<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 07/07/14
 * Time: 14:07.
 */
namespace Sygefor\Bundle\CoreBundle\Entity\Term;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\UploadableTrait;
use Sygefor\Bundle\CoreBundle\Form\Type\PublipostTemplateVocabularyType;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Class PublipostTemplates.
 *
 * @ORM\Table(name="publipost_template")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class PublipostTemplate extends AbstractTerm implements VocabularyInterface
{
    use UploadableTrait;

    /**
     * @ORM\Column(name="entity", type="text", nullable=false)
     * @Assert\NotNull()
     *
     * @var string
     */
    protected $entity;

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return 'Modèles de publipostage';
    }

    /**
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * returns the form type name for template edition.
     *
     * @return string
     */
    public static function getFormType()
    {
        return PublipostTemplateVocabularyType::class;
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_LOCAL;
    }

    /**
     * @Assert\Callback()
     */
    public function validateFile(ExecutionContext $context)
    {
        if (empty($this->file)) {
            $context->addViolationAt('file', 'Vous devez sélectionner un fichier');
        }
    }

    /**
     * @return string
     */
    protected function getTemplatesRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__ . '/../../../../../../app/Resources/Templates/Publipost';
    }

    /**
     * @return mixed
     */
    public static function orderBy()
    {
        return 'name';
    }
}
