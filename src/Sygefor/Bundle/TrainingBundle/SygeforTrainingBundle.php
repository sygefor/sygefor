<?php

namespace Sygefor\Bundle\TrainingBundle;

use Sygefor\Bundle\TrainingBundle\DependencyInjection\Compiler\TrainingTypeRegistryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SygeforTrainingBundle
 * @package Sygefor\Bundle\TrainingBundle
 */
class SygeforTrainingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new TrainingTypeRegistryPass());
    }
}
