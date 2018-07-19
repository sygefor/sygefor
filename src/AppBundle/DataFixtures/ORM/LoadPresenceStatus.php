<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 02/06/14
 * Time: 14:39.
 */

namespace AppBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\CoreBundle\Entity\Term\PresenceStatus;

/**
 * Class LoadPresenceStatus.
 */
class LoadPresenceStatus extends AbstractTermLoad
{
    public static $class = PresenceStatus::class;

    public function getTerms()
    {
        return array(
            array(
                'name' => 'Présent',
                'status' => PresenceStatus::STATUS_PRESENT,
            ),
            array(
                'name' => 'Partiel',
                'status' => PresenceStatus::STATUS_PRESENT,
            ),
            array(
                'name' => 'Absent',
                'status' => PresenceStatus::STATUS_ABSENT,
            ),
            array(
                'name' => 'Excusé(e)',
                'status' => PresenceStatus::STATUS_ABSENT,
            ),
        );
    }
}
