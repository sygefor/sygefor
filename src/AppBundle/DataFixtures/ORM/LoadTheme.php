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
            array('name' => 'Connaissances scientifiques'),
            array('name' => 'Techniques spécifiques'),
            array('name' => 'Informatique (conception des outils)'),
            array('name' => 'Bureautique (utilisation des outils)'),
            array('name' => 'Utilisation d\'applications spécialisées CNRS'),
            array('name' => 'Prévention et sécurité / Ethique'),
            array('name' => 'Partenariat et valorisation'),
            array('name' => 'Finances, comptabilité'),
            array('name' => 'Resources humaines'),
            array('name' => 'Management / Qualité'),
            array('name' => 'Culture institutionnelle et efficacité personelle'),
            array('name' => 'Langues'),
        );
    }
}
