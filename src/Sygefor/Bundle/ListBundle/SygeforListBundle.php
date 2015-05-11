<?php

namespace Sygefor\Bundle\ListBundle;

use Sygefor\Bundle\ListBundle\DependencyInjection\compiler\BatchOperationRegistrationPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SygeforListBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new BatchOperationRegistrationPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
