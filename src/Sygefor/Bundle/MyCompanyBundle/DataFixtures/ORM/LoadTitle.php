<?php

namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait\Term\Title;

/**
 * Class LoadTitle.
 */
class LoadTitle extends AbstractTermLoad
{
    static $class = Title::class;

    function getTerms()
    {
        return array('Monsieur', 'Madame');
    }
}
