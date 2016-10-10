<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\InstitutionBundle\Entity\Term\InstitutionType;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

class LoadInstitutionType extends AbstractTermLoad
{
    static $class = InstitutionType::class;

    public function getTerms()
    {
        return array();
    }

    function getOrder()
    {
        return -1;
    }
}
