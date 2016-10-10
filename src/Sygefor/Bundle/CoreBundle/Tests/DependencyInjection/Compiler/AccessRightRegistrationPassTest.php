<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 17/03/14
 * Time: 10:45.
 */
namespace Sygefor\CoreBundle\Test\DependencyInjection\Compiler;

use Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler\AccessRightRegistrationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AccessRightRegistrationPassTest.
 */
class AccessRightRegistrationPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testProcessShouldRegisterTaggedProviders.
     */
    public function testProcessShouldRegisterTaggedProviders()
    {
        $container = new ContainerBuilder();
        $pass      = new AccessRightRegistrationPass();

        $registryDefinition = new Definition();

        $container->setDefinition('sygefor_core.access_right_registry', $registryDefinition);

        $container->setDefinition('sygefor_core.access_right_aa', $this->createProviderDefinition('sygefor_core.right_provider', array('group' => 'group A')));
        $container->setDefinition('sygefor_core.access_right_bb', $this->createProviderDefinition('sygefor_core.right_provider', array('group' => 'group B')));
        $container->setDefinition('sygefor_core.access_right_cc', $this->createProviderDefinition('sygefor_core.right_provider', array('group' => 'group C')));

        $pass->process($container);

        $calls = $registryDefinition->getMethodCalls();

        $this->assertSame(array('addAccessRight', array('sygefor_core.access_right_aa', new Reference('sygefor_core.access_right_aa'), 'group A')), $calls[0]);
        $this->assertSame(array('addAccessRight', array('sygefor_core.access_right_bb', new Reference('sygefor_core.access_right_bb'), 'group B')), $calls[1]);
        $this->assertSame(array('addAccessRight', array('sygefor_core.access_right_cc', new Reference('sygefor_core.access_right_cc'), 'group C')), $calls[2]);
        $this->assertSame(3, count($calls));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessShouldRequireProviderImplementation()
    {
        $container = new ContainerBuilder();
        $pass      = new AccessRightRegistrationPass();

        $container->setDefinition('sygefor_core.access_right_registry', new Definition());

        $providerDef = $this->createProviderDefinition('sygefor_core.right_provider');
        $providerDef->setClass('stdClass');

        $container->setDefinition('sygefor_core.access_right_registry.aa', $providerDef);

        $pass->process($container);

    }

    /**
     * @param string $tag
     * @param array  $attrs
     *
     * @return Definition
     */
    private function createProviderDefinition($tag, array $attrs = array())
    {
        $provider   = $this->getMock('Sygefor\Bundle\CoreBundle\AccessRight\AccessRightInterface');
        $definition = new Definition(get_class($provider));
        $definition->addTag($tag, $attrs);

        return $definition;
    }
}
