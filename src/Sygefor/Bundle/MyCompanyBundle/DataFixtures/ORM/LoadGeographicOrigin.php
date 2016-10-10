<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:47.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\InstitutionBundle\Entity\Term\GeographicOrigin;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

class LoadGeographicOrigin extends AbstractTermLoad
{
    static $class = GeographicOrigin::class;

    public function getTerms()
    {
        return array(
            array(
                'name'     => 'Agglomération',
                'position' => 1,
            ),
            array(
                'name'        => 'Etablissement de rattachement',
                'machineName' => 'default',
            ),
            'Hors zone',
            'Zone de compétence',
        );
    }
}
