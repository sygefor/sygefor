<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 27/05/14
 * Time: 16:43.
 */
namespace Sygefor\Bundle\CoreBundle\Entity\PersonTrait\Term;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;

/**
 * Civilité.
 *
 * @ORM\Table(name="title")
 * @ORM\Entity
 */
class Title extends AbstractTerm implements VocabularyInterface
{
    /**
     * This term is required during term replacement.
     *
     * @var bool
     */
    static $replacementRequired = true;

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return 'Civilités';
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
