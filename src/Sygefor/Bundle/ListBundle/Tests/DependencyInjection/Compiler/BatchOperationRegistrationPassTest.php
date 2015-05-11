<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 09/04/14
 * Time: 17:29
 */

namespace Sygefor\Bundle\ListBundle\Tests\DependencyInjection\Compiler;


use Sygefor\Bundle\ListBundle\DependencyInjection\compiler\BatchOperationRegistrationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BatchOperationRegistrationPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testProcessShouldNotRegisterMissTaggedProviders
     */
    public function testProcessShouldNotRegisterMisTaggedProviders()
    {
        $container = new ContainerBuilder();
        $pass = new BatchOperationRegistrationPass();

        $registryDefinition = new Definition();

        $container->setDefinition('sygefor_list.batch_operation_registry', $registryDefinition);

        $container->setDefinition('sygefor_list.batch_operation_foo', $this->createProviderDefinition('sygefor_list.batch_operation_providedfr',array( 'label' => 'label 1', 'class' => 'fooAndBar')));
        $container->setDefinition('sygefor_list.batch_operation_bar', $this->createProviderDefinition('sygefor_list.batch_operation_providder',array( 'label' => 'label 2', 'class' => 'fooAndBar')));

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
        $pass = new BatchOperationRegistrationPass();

        $registryDefinition = new Definition();

        $container->setDefinition('sygefor_list.batch_operation_registry', $registryDefinition);

        $container->setDefinition('sygefor_list.batch_operation_foo', $this->createProviderDefinition('sygefor_list.batch_operation_provider',array( 'label' => 'label 1', 'class' => 'fooAndBar' )));
        $container->setDefinition('sygefor_list.batch_operation_bar', $this->createProviderDefinition('sygefor_list.batch_operation_provider',array( 'label' => 'label 1', 'class' => 'fooAndBar' )));
        //print_r ($container ) ;

        $pass->process($container);

        $calls = $registryDefinition->getMethodCalls();

        $this->assertEquals(array('addBatchOperation',array(new Reference('sygefor_list.batch_operation_foo'), 'sygefor_list.batch_operation_foo')), $calls[0]);
        $this->assertEquals(array('addBatchOperation',array(new Reference('sygefor_list.batch_operation_bar'), 'sygefor_list.batch_operation_bar')), $calls[1]);
        $this->assertEquals(2,count($calls));

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessShouldRequireProviderImplementation()
    {
        $container = new ContainerBuilder();
        $pass = new BatchOperationRegistrationPass();

        $container->setDefinition('sygefor_list.batch_operation_registry', new Definition()) ;

        $providerDef = $this->createProviderDefinition('sygefor_list.batch_operation_provider');
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
        //$provider = $this->getMockForAbstractClass('Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation');
        $definition = new Definition('Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation');
        $definition->addTag($tag, $attrs);

        return $definition;
    }
} 