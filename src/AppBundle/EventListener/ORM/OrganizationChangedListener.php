<?php

namespace AppBundle\EventListener\ORM;

use AppBundle\Entity\Trainer;
use Sygefor\Bundle\CoreBundle\EventListener\ORM\OrganizationChangedListener as CoreOrganizationChangedListener;

class OrganizationChangedListener extends CoreOrganizationChangedListener
{
    /**
     * Exclude some check because we want to keep them if organization changes.
     *
     * @param $class
     *
     * @return array
     */
    protected function getExcludedProperties($class)
    {
        $excludedProperties = array(
          Trainer::class => array(
              'participations',
          ),
        );

        if (isset($excludedProperties[$class])) {
            return $excludedProperties[$class];
        }

        return array();
    }
}
