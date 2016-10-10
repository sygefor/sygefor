<?php

namespace Sygefor\Bundle\CoreBundle;

use Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler\AccessRightRegistrationPass;
use Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler\BatchOperationRegistrationPass;
use Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler\DisableListenersPass;
use Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler\DynamicMappingPass;
use Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler\MappingProviderPass;
use Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler\ReplaceTransformerClassPass;
use Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler\VocabularyRegistrationPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SygeforCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // elastica compiler pass
        $container->addCompilerPass(new DisableListenersPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $container->addCompilerPass(new ReplaceTransformerClassPass());
        $container->addCompilerPass(new DynamicMappingPass());
        $container->addCompilerPass(new MappingProviderPass());

        // access right compiler pass
        $container->addCompilerPass(new AccessRightRegistrationPass());

        // batch operation compiler pass
        $container->addCompilerPass(new BatchOperationRegistrationPass(), PassConfig::TYPE_BEFORE_REMOVING);

        // taxonomy compiler pass
        $container->addCompilerPass(new VocabularyRegistrationPass());
    }

    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
