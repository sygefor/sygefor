<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\TrainingBundle\Entity\Session\Term\SessionType;

class LoadSessionType extends AbstractTermLoad
{
    static $class = SessionType::class;

    public function getTerms()
    {
        return array();
    }
}
