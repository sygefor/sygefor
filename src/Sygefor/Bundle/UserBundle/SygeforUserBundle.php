<?php

namespace Sygefor\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Sygefor\Bundle\UserBundle\DependencyInjection\Compiler\AccessRightRegistrationPass ;

/**
 * Class SygeforUserBundle
 * @package Sygefor\Bundle\UserBundle
 */
class SygeforUserBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AccessRightRegistrationPass());
    }

    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
