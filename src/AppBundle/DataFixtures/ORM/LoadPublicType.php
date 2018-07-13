<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */

namespace AppBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use AppBundle\Entity\Term\PublicType;

class LoadPublicType extends AbstractTermLoad
{
    public static $class = PublicType::class;

    public function getTerms()
    {
        return array(
            'Chargé de Recherche',
            'Directeur de recherche',
            'Doctorant',
            'Enseignant chercheur',
            'Ingénieur',
            'Technicien',
            'Autre',
        );
    }
}
