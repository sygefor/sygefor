<?php

namespace AppBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use AppBundle\Entity\Term\Trainee\Disciplinary;

/**
 * Class LoadDisciplinary.
 */
class LoadDisciplinary extends AbstractTermLoad
{
    public static $class = Disciplinary::class;

    public function getTerms()
    {
        return array();
    }
}
