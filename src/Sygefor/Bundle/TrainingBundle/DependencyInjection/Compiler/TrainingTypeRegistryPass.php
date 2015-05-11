<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sygefor\Bundle\TrainingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds all services with the tags "sygefor_training.type" as
 * arguments of the "sygefor_training.type.registry" service
 */
class TrainingTypeRegistryPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        //changing class for semestered training publipost service
        if ($container->hasDefinition('sygefor_list.batch.publipost.semestered_training')) {
            $serviceDef = $container->getDefinition('sygefor_list.batch.publipost.semestered_training');
            $serviceDef->setClass('Sygefor\Bundle\TrainingBundle\BatchOperations\SemesteredTrainingMailingBatchOperation');
        }

        //changing class for semestered training publipost service
        if ($container->hasDefinition('sygefor_list.batch.csv.semestered_training')) {
            $serviceDef = $container->getDefinition('sygefor_list.batch.csv.semestered_training');
            $serviceDef->setClass('Sygefor\Bundle\TrainingBundle\BatchOperations\SemesteredTrainingCSVBatchOperation');
        }

        //
        if (!$container->hasDefinition('sygefor_training.type.registry')) {
            return;
        }

        $definition = $container->getDefinition('sygefor_training.type.registry');

        // Builds an array with service IDs as keys and tag aliases as values
        $types = array();
        foreach ($container->findTaggedServiceIds('sygefor_training.type') as $serviceId => $tag) {
            $def = $container->getDefinition($serviceId);
            $class = $def->getClass();
            $type = isset($tag[0]['alias']) ? $tag[0]['alias'] : $class::getType();
            $types[$type] = array(
                'class' => $class,
                'label' => $alias = isset($tag[0]['label']) ? $tag[0]['label'] : $class::getTypeLabel()
            );
        }
        $definition->replaceArgument(0, $types);
    }
}
