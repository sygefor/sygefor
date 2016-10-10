<?php

namespace Sygefor\Bundle\TraineeBundle\Entity\Term;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\Term\TreeTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Theme
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="disciplinary")
 * @ORM\Entity
 */
class Disciplinary extends AbstractTerm implements VocabularyInterface
{
    use TreeTrait;

    /**
     * This term is required during term replacement
     * @var bool
     */
    static $replacementRequired = true;

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return "Disciplines";
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
