<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\MyCompanyBundle\Entity\Term\Theme;

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
            'Informatique (conception des outils)',
            'Bureautique (utilisation des outils)',
            'Utilisation d\'applications spécialisées CNRS',
            'Prévention et sécurité / Ethique',
            'Partenariat et valorisation',
            'Finances, comptabilité',
            'Resources humaines',
            'Management / Qualité',
            'Culture institutionnelle et efficacité personelle',
            'Langues',
        );
    }
}
