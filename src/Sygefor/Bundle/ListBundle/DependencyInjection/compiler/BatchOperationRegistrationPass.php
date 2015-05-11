<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 07/04/14
 * Time: 11:22
 */

namespace Sygefor\Bundle\ListBundle\DependencyInjection\compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BatchOperationRegistrationPass implements CompilerPassInterface{

    /**
     * Process the compiler pass
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sygefor_list.batch_operation_registry')) {
            return;
        }
        $definition = $container->getDefinition('sygefor_list.batch_operation_registry');
        $batchOpServices = $container->findTaggedServiceIds('sygefor_list.batch_operation_provider');

        foreach ($batchOpServices as $id => $tagAttributes) {
            //checking class
            $class = $container->getDefinition($id)->getClass();
            if (!$class || !$this->isBatchOperationImplementation($class)) {
                throw new \InvalidArgumentException(sprintf('Batch Operation Registration : %s must implement BatchOperationInterface', $class));
            }

            if(!$container->getDefinition($id)->isAbstract()) {
                $definition->addMethodCall('addBatchOperation', array(new Reference($id), $id));
            }
        }
    }

    private function isBatchOperationImplementation($class)
    {
        $refl = new \ReflectionClass($class);
        return $refl->implementsInterface('Sygefor\Bundle\ListBundle\BatchOperation\BatchOperationInterface');
    }
}
