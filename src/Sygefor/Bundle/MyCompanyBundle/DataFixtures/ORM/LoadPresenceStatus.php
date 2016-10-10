<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 02/06/14
 * Time: 14:39.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\PresenceStatus;

/**
 * Class LoadPresenceStatus.
 */
class LoadPresenceStatus extends AbstractTermLoad
{
    static $class = PresenceStatus::class;

    function getTerms()
    {
        return array(
            array(
                'name'   => 'Présent',
                'status' => PresenceStatus::STATUS_PRESENT,
            ),
            array(
                'name'   => 'Partiel',
                'status' => PresenceStatus::STATUS_PRESENT,
            ),
            array(
                'name'   => 'Absent',
                'status' => PresenceStatus::STATUS_ABSENT,
            ),
            array(
                'name'   => 'Excusé(e)',
                'status' => PresenceStatus::STATUS_ABSENT,
            ),
        );
    }
}
