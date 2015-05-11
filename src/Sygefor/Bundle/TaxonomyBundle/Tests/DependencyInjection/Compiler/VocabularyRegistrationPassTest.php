<?php

namespace Sygefor\UserBundle\Test\DependencyInjection\Compiler;

use Sygefor\Bundle\TaxonomyBundle\DependencyInjection\Compiler\VocabularyRegistrationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class VocabularyRegistrationPassTest
 * @package Sygefor\TaxonomyBundle\Test\DependencyInjection\Compiler
 */
class VocabularyRegistrationPassTest extends \PHPUnit_Framework_TestCase
{
    /**
    * testProcessShouldNotRegisterMissTaggedProviders
    */
    public function testProcessShouldNotRegisterMissTaggedProviders()
    {
        $container = new ContainerBuilder();
        $pass = new VocabularyRegistrationPass();

        $registryDefinition = new Definition();

        $container->setDefinition('sygefor_taxonomy.vocabulary_registr', $registryDefinition);

        $container->setDefinition('sygefor_taxonomy.vocabulary_foo', $this->createProviderDefinition('sygefor_taxonomy.vocabulary_provider',array( 'group' => 'fooAndBar')));
        $container->setDefinition('sygefor_taxonomy.vocabulary_bar', $this->createProviderDefinition('sygefor_taxonomy.vocabulary_provider',array( 'group' => 'fooAndBar')));

        $pass->process($container);

        $calls = $registryDefinition->getMethodCalls();

        $this->assertEquals(0,count($calls));

    }
    /**
     * testProcessShouldRegisterTaggedProviders
     */
    public function testProcessShouldRegisterTaggedProviders()
    {
        $container = new ContainerBuilder();
        $pass = new VocabularyRegistrationPass();

        $registryDefinition = new Definition();

        $container->setDefinition('sygefor_taxonomy.vocabulary_registry', $registryDefinition);

        $container->setDefinition('sygefor_taxonomy.vocabulary_foo', $this->createProviderDefinition('sygefor_taxonomy.vocabulary_provider',array( 'group' => 'fooAndBar')));
        $container->setDefinition('sygefor_taxonomy.vocabulary_bar', $this->createProviderDefinition('sygefor_taxonomy.vocabulary_provider',array( 'group' => 'fooAndBar')));

        $pass->process($container);

        $calls = $registryDefinition->getMethodCalls();

        $this->assertEquals(array('addVocabulary',array(new Reference('sygefor_taxonomy.vocabulary_foo'), 'sygefor_taxonomy.vocabulary_foo', 'fooAndBar')), $calls[0]);
        $this->assertEquals(array('addVocabulary',array(new Reference('sygefor_taxonomy.vocabulary_bar'), 'sygefor_taxonomy.vocabulary_bar', 'fooAndBar')), $calls[1]);
        $this->assertEquals(2,count($calls));

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessShouldRequireProviderImplementation()
    {
        $container = new ContainerBuilder();
        $pass = new VocabularyRegistrationPass();

        $container->setDefinition('sygefor_taxonomy.vocabulary_registry', new Definition()) ;

        $providerDef = $this->createProviderDefinition('sygefor_taxonomy.vocabulary_provider');
        $providerDef->setClass('stdClass');

        $container->setDefinition('sygefor_taxonomy.vocabulary_registry.foo', $providerDef);

        $pass->process($container);

    }

    /**
     * @param String $tag
     * @param array $attrs
     * @return Definition
     */
    private function createProviderDefinition($tag, array $attrs = array())
    {
        $provider = $this->getMock('Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface');
        $definition = new Definition(get_class($provider));
        $definition->addTag($tag, $attrs);
        return $definition;
    }

}
