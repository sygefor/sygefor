<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Term\Session\Place;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

class LoadPlace extends AbstractTermLoad
{
    public static $class = Place::class;

    public function getTerms()
    {
        $places = array();

        foreach ($this->organizations as $organization) {
            $places[] = [
                'name' => $organization->getName(),
                'organization' => $organization,
            ];
        }

        return $places;
    }
}
