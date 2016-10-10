<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 01/09/14
 * Time: 10:23.
 */
namespace Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DisableListenersPass.
 */
class ReplaceTransformerClassPass implements CompilerPassInterface
{
    /**
     * Process the compiler pass.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // replace the fos_elastica.model_to_elastica_transformer.class parameter
        $container->setParameter('fos_elastica.model_to_elastica_transformer.class', 'Sygefor\Bundle\CoreBundle\Transformer\ModelToElasticaTransformer');
    }
}
