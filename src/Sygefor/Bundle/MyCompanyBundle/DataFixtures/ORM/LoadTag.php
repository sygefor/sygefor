<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Tag;

/**
 * Class LoadTag.
 */
class LoadTag extends AbstractTermLoad
{
    static $class = Tag::class;

    public function getTerms()
    {
        return array();
    }
}
