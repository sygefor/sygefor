<?php

namespace Sygefor\Bundle\TraineeBundle\DataFixtures\ORM;

use AppBundle\Entity\Term\Trainee\PublicCategory;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

/**
 * Class LoadPublicType.
 */
class LoadPublicCategory extends AbstractTermLoad
{
    static $class = PublicCategory::class;

    public function getTerms()
    {
        return array(
            'Catégorie A',
            'Catégorie B',
            'Catégorie C',
        );
    }
}
