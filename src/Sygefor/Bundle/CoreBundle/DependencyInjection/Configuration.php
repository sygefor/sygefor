<?php

namespace Sygefor\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sygefor_core');

        $rootNode
            ->children()
            ->arrayNode('batch')//->defaultValue(array())
            ->children()
            ->append($this->getConvertTypeConfigTree())
            ->append($this->getCSVConfigTree())
            ->append($this->getChangeStatusConfigTree())
            ->append($this->getMailingConfigTree())
            ->append($this->getPDFConfigTree())
            ->end()
            ->end();

        return $treeBuilder;
    }

    private function getMailingConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('mailing');

        $node
            ->prototype('array')
            ->treatNullLike(array())
            ->children()
            ->scalarNode('parent')->end()
            ->scalarNode('emailPath')->end()
            ->scalarNode('excludeFromFormType')->end()
            ->scalarNode('alias')->end()
            ->scalarNode('class')->isRequired()->end()
            ->arrayNode('fields')
            ->prototype('array')
            ->beforeNormalization()
            ->ifString()
            ->then(function ($v) {
                return array('property' => $v);
            })
            ->end()
            ->children()
            ->scalarNode('property')->end()
            ->scalarNode('type')->end()
            ->scalarNode('format')->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $node;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getConvertTypeConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('convert_type');

        $node
            ->prototype('array')
            ->treatNullLike(array())
            ->children()
            ->scalarNode('class')->isRequired()->end()
            ->end()
            ->end();

        return $node;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getCSVConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('csv');

        $node
            ->prototype('array')
            ->treatNullLike(array())
            ->children()
            ->scalarNode('class')->isRequired()->end()
            ->scalarNode('filename')->defaultValue('export.csv')->end()
            ->arrayNode('fields')
            ->prototype('array')
            ->beforeNormalization()
            ->ifString()
            ->then(function ($v) {
                return array('label' => $v);
            })
            ->end()
            ->children()
            ->scalarNode('label')->isRequired()->end()
            ->scalarNode('type')->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $node;

    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getChangeStatusConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('change_status');

        $node
            ->prototype('array')
            ->treatNullLike(array())
            ->children()
            ->scalarNode('class')->isRequired()->end()
            ->end()
            ->end();

        return $node;

    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getPDFConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('pdf');

        $node
            ->prototype('array')
            ->treatNullLike(array())
            /*->beforeNormalization()
                ->ifString()
                    ->then(function($v) { return array('route'=> $v); })
                ->end()*/
            ->children()
            ->scalarNode('class')->isRequired()->end()
            ->scalarNode('template')->isRequired()->end()
            ->scalarNode('key')->defaultValue(null)->end()
            ->scalarNode('filename')->defaultValue(null)->end()
            ->scalarNode('templateDiscriminator')->defaultValue(null)->end()
            ->arrayNode('templates')
            ->prototype('scalar')->end()
            ->defaultValue(array())
            ->end()
            ->end()
            ->end();

        return $node;
    }
}
