<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 12/06/14
 * Time: 15:02.
 */
namespace Sygefor\Bundle\TrainingBundle\Tests\DependencyInjection\Compiler;

use Sygefor\Bundle\TrainingBundle\DependencyInjection\Compiler\TrainingTypeRegistryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class TrainingTypeRegistryPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * tests.
     */
    public function testPassCorrectlyAddsTrainingTypes()
    {
        $container = new ContainerBuilder();
        $pass      = new TrainingTypeRegistryPass();

        $registryDefinition = new Definition();
        $registryDefinition->addArgument('foo');

        $container->setDefinition('sygefor_training.type.registry', $registryDefinition);

        $container->setDefinition('sygefor_core.vocabulary_foo', $this->getTaggedTrainingTypeDefinition('sygefor_training.type', array('alias' => 'footype')));
        $container->setDefinition('sygefor_core.vocabulary_bar', $this->getTaggedTrainingTypeDefinition('sygefor_training.type', array('alias' => 'bartype')));

        $pass->process($container);

        $args = $registryDefinition->getArguments();
        $args = array_keys($args[0]);

        $this->assertSame(array('footype', 'bartype'), $args);
    }

    public function testCSVBatchOperationClassIsCorrectlySet()
    {
        $container = new ContainerBuilder();
        $pass      = new TrainingTypeRegistryPass();

        $registryDefinition = new Definition();
        $registryDefinition->addArgument('foo');

        $container->setDefinition('sygefor_core.batch.csv.semestered_training', $registryDefinition);

        //---

        $pass->process($container);

        $this->assertSame('Sygefor\Bundle\TrainingBundle\BatchOperations\SemesteredTrainingCSVBatchOperation', $registryDefinition->getClass());
    }

    public function testMailingBatchOperationClassIsCorrectlySet()
    {
        $container = new ContainerBuilder();
        $pass      = new TrainingTypeRegistryPass();

        $registryDefinition = new Definition();
        $registryDefinition->addArgument('foo');

        $container->setDefinition('sygefor_core.batch.publipost.semestered_training', $registryDefinition);

        //---

        $pass->process($container);

        $this->assertSame('Sygefor\Bundle\TrainingBundle\BatchOperations\SemesteredTrainingMailingBatchOperation', $registryDefinition->getClass());
    }

    private function getTaggedTrainingTypeDefinition($tag, $attrs)
    {
        $class = $this->getMockForAbstractClass('Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining');

        $definition = new Definition(get_class($class));
        $definition->addTag($tag, $attrs);

        return $definition;
    }

}
