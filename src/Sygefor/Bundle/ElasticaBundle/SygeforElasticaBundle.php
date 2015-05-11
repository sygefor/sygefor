<?php

namespace Sygefor\Bundle\ElasticaBundle;

use Sygefor\Bundle\ElasticaBundle\DependencyInjection\Compiler\DisableListenersPass;
use Sygefor\Bundle\ElasticaBundle\DependencyInjection\Compiler\DynamicMappingPass;
use Sygefor\Bundle\ElasticaBundle\DependencyInjection\Compiler\ReplaceTransformerClassPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SygeforElasticaBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new DisableListenersPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $container->addCompilerPass(new ReplaceTransformerClassPass());
        $container->addCompilerPass(new DynamicMappingPass());
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'FOSElasticaBundle';
    }
}
