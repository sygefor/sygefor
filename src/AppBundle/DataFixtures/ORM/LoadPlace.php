<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Organization;
use AppBundle\Entity\Term\Session\Place;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

class LoadPlace extends AbstractTermLoad
{
    public static $class = Place::class;

    public function getTerms()
    {
        $dr01 = null;
        $places = array();

        /** @var Organization $organization */
        foreach ($this->organizations as $organization) {
            if ($organization->getDrCode() === 1) {
                $dr01 = $organization;
                break;
            }
        }

        foreach ($this->organizations as $organization) {
            if ($dr01 === null || $organization->getId() !== $dr01->getId()) {
                $places[] = [
                    'name' => $organization->getName(),
                    'organization' => $organization,
                ];
            }
        }

        return $places;
    }
}
