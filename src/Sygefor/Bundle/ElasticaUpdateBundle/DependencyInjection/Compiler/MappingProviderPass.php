<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 01/09/14
 * Time: 10:23
 */

namespace Sygefor\Bundle\ElasticaUpdateBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MappingProviderPass
 * @package Sygefor\Bundle\ElasticaUpdateBundle\DependencyInjection\Compiler
 */
class MappingProviderPass implements CompilerPassInterface
{
    /**
     * Process the compiler pass
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // extract current config source
        $sourceConfigs = $container->getDefinition('fos_elastica.config_source.container')->getArgument(0);
        $container->getDefinition("sygefor_elastica_update.elastica_mapping_provider")->replaceArgument(0, $sourceConfigs);
    }
}
