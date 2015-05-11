<?php

namespace Sygefor\Bundle\ElasticaUpdateBundle;

use Sygefor\Bundle\ElasticaUpdateBundle\DependencyInjection\Compiler\MappingProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SygeforElasticaUpdateBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new MappingProviderPass());
    }
}
