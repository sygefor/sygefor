<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 6/17/16
 * Time: 5:31 PM.
 */
namespace AppBundle\Entity\Term\Session;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Entity\Term\VocabularyInterface;

/**
 * Type de session.
 *
 * @ORM\Table(name="session_type")
 * @ORM\Entity
 */
class SessionType extends AbstractTerm implements VocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return 'Type de session';
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}
