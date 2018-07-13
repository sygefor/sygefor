<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Term\Training\Theme;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

/**
 * Class LoadTheme.
 */
class LoadTheme extends AbstractTermLoad
{
    public static $class = Theme::class;

    public function getTerms()
    {
        return array(
            array('code' => 'SCI', 'name' => 'Connaissances scientifiques'),
            array('code' => 'TEC', 'name' => 'Techniques spécifiques'),
            array('code' => 'INF', 'name' => 'Informatique (conception des outils)'),
            array('code' => 'BUR', 'name' => 'Bureautique (utilisation des outils)'),
            array('code' => 'APS', 'name' => 'Utilisation d\'applications spécialisées CNRS'),
            array('code' => 'SST', 'name' => 'Prévention et sécurité / Ethique'),
            array('code' => 'SPV', 'name' => 'Partenariat et valorisation'),
            array('code' => 'SFC', 'name' => 'Finances, comptabilité'),
            array('code' => 'SRH', 'name' => 'Resources humaines'),
            array('code' => 'MGQ', 'name' => 'Management / Qualité'),
            array('code' => 'EFF', 'name' => 'Culture institutionnelle et efficacité personelle'),
            array('code' => 'LAN', 'name' => 'Langues'),
        );
    }
}
