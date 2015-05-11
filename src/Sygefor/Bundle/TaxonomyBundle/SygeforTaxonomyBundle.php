<?php

namespace Sygefor\Bundle\TaxonomyBundle;

use Sygefor\Bundle\TaxonomyBundle\DependencyInjection\Compiler\VocabularyRegistrationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SygeforTaxonomydBundle
 * @package Sygefor\Bundle\TaxonomyBundle
 */
class SygeforTaxonomyBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new VocabularyRegistrationPass());
    }
}