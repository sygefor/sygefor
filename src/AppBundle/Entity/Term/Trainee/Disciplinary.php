<?php

namespace AppBundle\Entity\Term\Trainee;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Entity\Term\TreeTrait;
use Sygefor\Bundle\CoreBundle\Entity\Term\VocabularyInterface;

/**
 * Theme.
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="disciplinary")
 * @ORM\Entity
 */
class Disciplinary extends AbstractTerm implements VocabularyInterface
{
    use TreeTrait;

    /**
     * This term is required during term replacement.
     *
     * @var bool
     */
    public static $replacementRequired = true;

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return 'Disciplines';
    }

    /**
     * @return mixed
     *               This static method is used to set a specific order field
     *               when fetch terms
     */
    public static function orderBy()
    {
        return 'position';
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
