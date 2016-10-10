<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Theme;

/**
 * Class LoadTheme.
 */
class LoadTheme extends AbstractTermLoad
{
    static $class = Theme::class;

    public function getTerms()
    {
        return array(
            'Connaissances scientifiques',
            'Techniques spécifiques',
            'Prévention et sécurité / Ethique',
            'Finances, comptabilité',
            'Culture institutionnelle et efficacité personelle'
        );
    }
}
