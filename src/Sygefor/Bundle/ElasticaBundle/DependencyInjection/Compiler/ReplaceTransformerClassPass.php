<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 01/09/14
 * Time: 10:23
 */

namespace Sygefor\Bundle\ElasticaBundle\DependencyInjection\Compiler;

use Sygefor\Bundle\ElasticaBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DisableListenersPass
 * @package Sygefor\Bundle\ElasticaUpdateBundle\DependencyInjection\Compiler
 */
class ReplaceTransformerClassPass implements CompilerPassInterface
{
    /**
     * Process the compiler pass
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // replace the fos_elastica.model_to_elastica_transformer.class parameter
        $container->setParameter('fos_elastica.model_to_elastica_transformer.class', 'Sygefor\Bundle\ElasticaBundle\Transformer\ModelToElasticaTransformer');
    }
}
