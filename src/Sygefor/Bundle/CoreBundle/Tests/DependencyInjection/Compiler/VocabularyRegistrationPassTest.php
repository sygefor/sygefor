<?php

namespace Sygefor\CoreBundle\Test\DependencyInjection\Compiler;

use Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler\VocabularyRegistrationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class VocabularyRegistrationPassTest.
 */
class VocabularyRegistrationPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testProcessShouldNotRegisterMissTaggedProviders.
     */
    public function testProcessShouldNotRegisterMissTaggedProviders()
    {
        $container = new ContainerBuilder();
        $pass      = new VocabularyRegistrationPass();

        $registryDefinition = new Definition();

        $container->setDefinition('sygefor_core.vocabulary_registr', $registryDefinition);

        $container->setDefinition('sygefor_core.vocabulary_foo', $this->createProviderDefinition('sygefor_core.vocabulary_provider', array('group' => 'fooAndBar')));
        $container->setDefinition('sygefor_core.vocabulary_bar', $this->createProviderDefinition('sygefor_core.vocabulary_provider', array('group' => 'fooAndBar')));

        $pass->process($container);

        $calls = $registryDefinition->getMethodCalls();

        $this->assertSame(0, count($calls));
    }

    /**
     * testProcessShouldRegisterTaggedProviders.
     */
    public function testProcessShouldRegisterTaggedProviders()
    {
        $container = new ContainerBuilder();
        $pass      = new VocabularyRegistrationPass();

        $registryDefinition = new Definition();

        $container->setDefinition('sygefor_core.vocabulary_registry', $registryDefinition);

        $container->setDefinition('sygefor_core.vocabulary_foo', $this->createProviderDefinition('sygefor_core.vocabulary_provider', array('group' => 'fooAndBar')));
        $container->setDefinition('sygefor_core.vocabulary_bar', $this->createProviderDefinition('sygefor_core.vocabulary_provider', array('group' => 'fooAndBar')));

        $pass->process($container);

        $calls = $registryDefinition->getMethodCalls();

        $this->assertSame(array('addVocabulary', array(new Reference('sygefor_core.vocabulary_foo'), 'sygefor_core.vocabulary_foo', 'fooAndBar')), $calls[0]);
        $this->assertSame(array('addVocabulary', array(new Reference('sygefor_core.vocabulary_bar'), 'sygefor_core.vocabulary_bar', 'fooAndBar')), $calls[1]);
        $this->assertSame(2, count($calls));

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessShouldRequireProviderImplementation()
    {
        $container = new ContainerBuilder();
        $pass      = new VocabularyRegistrationPass();

        $container->setDefinition('sygefor_core.vocabulary_registry', new Definition());

        $providerDef = $this->createProviderDefinition('sygefor_core.vocabulary_provider');
        $providerDef->setClass('stdClass');

        $container->setDefinition('sygefor_core.vocabulary_registry.foo', $providerDef);

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
        $provider   = $this->getMock('Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface');
        $definition = new Definition(get_class($provider));
        $definition->addTag($tag, $attrs);

        return $definition;
    }
}
