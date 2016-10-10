<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 02/06/14
 * Time: 14:39.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\TrainerBundle\Entity\Term\TrainerType;

/**
 * Class LoadCompetitionStatus.
 */
class LoadTrainerType extends AbstractTermLoad
{
    static $class = TrainerType::class;

    function getTerms()
    {
        return array();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    function getOrder()
    {
        return 2;
    }
}
