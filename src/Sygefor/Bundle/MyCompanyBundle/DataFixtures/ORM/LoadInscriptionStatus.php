<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 02/06/14
 * Time: 14:41.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\InscriptionStatus;

/**
 * Class LoadInscriptionStatus.
 */
class LoadInscriptionStatus extends AbstractTermLoad
{
    static $class = InscriptionStatus::class;

    function getTerms()
    {
        return array(
            array(
                'name'        => 'En attente',
                'status'      => InscriptionStatus::STATUS_PENDING,
                'notify'      => false,
                'machineName' => 'waiting',
            ),
            array(
                'name'        => 'Liste d\'attente',
                'status'      => InscriptionStatus::STATUS_WAITING,
                'notify'      => false,
                'machineName' => null,
            ),
            array(
                'name'        => 'Refusé',
                'status'      => InscriptionStatus::STATUS_REJECTED,
                'notify'      => false,
                'machineName' => null,
            ),
            array(
                'name'        => 'Accepté',
                'status'      => InscriptionStatus::STATUS_ACCEPTED,
                'notify'      => false,
                'machineName' => 'accept',
            ),
            array(
                'name'        => 'Désistement',
                'status'      => InscriptionStatus::STATUS_REJECTED,
                'notify'      => true,
                'machineName' => 'desist',
            ),
        );
    }
}
