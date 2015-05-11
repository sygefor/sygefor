<?php
namespace Sygefor\Bundle\CoreBundle\DataFixtures;

/**
 * Class AbstractTestDataFixture
 * @package Sygefor\Bundle\CoreBundle\DataFixtures
 */
abstract class AbstractTestDataFixture extends AbstractDataFixture
{
    /**
     * {@inheritdoc}
     */
    protected function getEnvironments() {
        return array('test');
    }

    /**
     * {@inheritdoc}
     */
    function getOrder() {
        return 1;
    }
}
