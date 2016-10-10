<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 01/09/14
 * Time: 10:23.
 */
namespace Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler;

use Sygefor\Bundle\CoreBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DisableListenersPass.
 */
class DisableListenersPass implements CompilerPassInterface
{
    /**
     * Process the compiler pass.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // read the config
        $configs   = $container->getExtensionConfig('sygefor_elastica');
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), $configs);

        // add 'fos_elastica.orm.listener' tag to all default listeners
        foreach($container->getServiceIds() as $id) {
            // remove all the elastica listeners
            if(preg_match('/^fos_elastica.listener./', $id) && $id !== 'fos_elastica.listener.prototype.orm') {
                $container->getDefinition($id)->addTag('fos_elastica.listener');
            }
        }

        // if the listeners has been disabled
        if($config && !$config['listeners']) {
            // remove all the tagged listeners
            $services = $container->findTaggedServiceIds('fos_elastica.listener');
            foreach($services as $id => $tags) {
                $container->removeDefinition($id);
            }
        }
    }
}
