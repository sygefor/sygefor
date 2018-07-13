<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 4/7/17
 * Time: 2:40 PM.
 */

namespace AppBundle\Entity\Term\Training;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Entity\Term\VocabularyInterface;

/**
 * Theme.
 *
 * @ORM\Table(name="theme")
 * @ORM\Entity
 */
class Theme extends AbstractTerm implements VocabularyInterface
{
    /**
     * This term is required during term replacement.
     *
     * @var bool
     */
    public static $replacementRequired = true;

    public static function orderBy()
    {
        return 'id';
    }

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return 'Thème';
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
