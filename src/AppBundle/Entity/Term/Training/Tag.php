<?php

namespace AppBundle\Entity\Term\Training;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Entity\Term\VocabularyInterface;

/**
 * Tag.
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity
 */
class Tag extends AbstractTerm implements VocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return 'Tags';
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_LOCAL;
    }
}
