<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\TrainingCategory;

class LoadTrainingCategory extends AbstractTermLoad
{
    static $class = TrainingCategory::class;

    public function getTerms()
    {
        return array(
            'Actions régionales',
            'Actions nationales'
        );
    }
}
