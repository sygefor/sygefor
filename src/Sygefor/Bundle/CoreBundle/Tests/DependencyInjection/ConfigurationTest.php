<?php

namespace Sygefor\Bundle\CoreBundle\Tests\DependencyInjection;

use Sygefor\Bundle\CoreBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function setUp()
    {
        $this->configuration = new Configuration(array());
    }

    public function testEmptyConfigContainsFormatMappingOptionNode()
    {
        $tree     = $this->configuration->getConfigTreeBuilder()->buildTree();
        $children = $tree->getChildren();

        $children = $children['batch']->getChildren();

        //$typeNodes = $children['types']->getPrototype()->getChildren();
        //$mappings = $typeNodes['mappings']->getPrototype()->getChildren();

        $this->assertArrayHasKey('csv', $children);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\PrototypedArrayNode', $children['csv']);

        $this->assertArrayHasKey('mailing', $children);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\PrototypedArrayNode', $children['mailing']);
        //$this->assertNull($mappings['format']->getDefaultValue());
    }
}
