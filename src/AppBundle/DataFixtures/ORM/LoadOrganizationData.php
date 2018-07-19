<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

/**
 * Class LoadOrganizationData.
 */
class LoadOrganizationData extends AbstractTermLoad
{
    public static $class = Organization::class;

    public function getTerms()
    {
        return array(
            array(
                'name' => 'Sygefor',
                'code' => 'sygefor',
                'email' => 'contact@sygefor.com',
                'address' => '',
                'zip' => '35000',
                'city' => 'Rennes',
                'phoneNumber' => '',
                'website' => 'http://www.conjecto.com',
                'trainee_registrable' => true,
            ),
        );
    }

    public function getOrder()
    {
        return 0;
    }
}
