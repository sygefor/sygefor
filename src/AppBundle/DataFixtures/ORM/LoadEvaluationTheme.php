<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 12/5/17
 * Time: 4:09 PM.
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Term\Evaluation\Theme;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

/**
 * Class LoadEvaluationTheme.
 */
class LoadEvaluationTheme extends AbstractTermLoad
{
    public static $class = Theme::class;

    public function getTerms()
    {
        return array(
            'Le contenu',
            'L\'animation',
            'La pédagogie',
            'L\'organisation matérielle',
        );
    }

    public function getOrder()
    {
        return 1;
    }
}
