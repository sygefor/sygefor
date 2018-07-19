<?php

namespace AppBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\CoreBundle\Entity\Term\Title;

/**
 * Class LoadTitle.
 */
class LoadTitle extends AbstractTermLoad
{
    public static $class = Title::class;

    public function getTerms()
    {
        return array('Monsieur', 'Madame');
    }
}
