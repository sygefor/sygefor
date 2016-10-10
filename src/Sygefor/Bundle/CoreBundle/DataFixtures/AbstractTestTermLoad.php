<?php

namespace Sygefor\Bundle\CoreBundle\DataFixtures;

/**
 * Created by PhpStorm.
 * User: Blaise
 * Date: 09/06/2016
 * Time: 12:24.
 */
abstract class AbstractTestTermLoad extends AbstractTermLoad
{
    /**
     * {@inheritdoc}
     */
    protected function getEnvironments()
    {
        return array('test');
    }
}
