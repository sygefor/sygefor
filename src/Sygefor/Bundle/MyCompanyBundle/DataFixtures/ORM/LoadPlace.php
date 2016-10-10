<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\TrainingBundle\Entity\Session\Term\Place;

class LoadPlace extends AbstractTermLoad
{
    static $class = Place::class;

    public function getTerms()
    {
        $places = array();
        foreach ($this->organizations as $organization) {
            $places[] = array(
                'name'         => $organization->getName(),
                'organization' => $organization,
            );
        }

        return $places;
    }
}
