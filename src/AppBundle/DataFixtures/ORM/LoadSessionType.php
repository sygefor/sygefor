<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */
namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;

use AppBundle\Entity\Term\Session\SessionType;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

class LoadSessionType extends AbstractTermLoad
{
    static $class = SessionType::class;

    public function getTerms()
    {
        return array('Session de sélection');
    }
}
