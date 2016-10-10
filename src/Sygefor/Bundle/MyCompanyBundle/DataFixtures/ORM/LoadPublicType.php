<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType;

class LoadPublicType extends AbstractTermLoad
{
    static $class = PublicType::class;

    public function getTerms()
    {
        return array(
            'Doctorant',
            'Post-doctorant',
            'Autre'
        );
    }
}
