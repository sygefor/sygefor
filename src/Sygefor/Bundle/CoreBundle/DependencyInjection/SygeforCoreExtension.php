<?php

namespace Sygefor\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SygeforCoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('batch.yml');
        $loader->load('forms.yml');
        $loader->load('elastica.yml');
        $loader->load('services.yml');
        $loader->load('taxonomy.yml');
        $loader->load('voters.yml');

        if (isset($config['batch'])) {
            $this->loadCSVServices($config['batch']['csv'], $container);
            $this->loadConvertTypeServices($config['batch']['convert_type'], $container);
            $this->loadPubliPostServices($config['batch']['mailing'], $container);
            $this->loadHumanReadablePropertyAccessor($config['batch']['mailing'], $container);
            $this->loadPDFServices($config['batch']['pdf'], $container);
        }
    }

    /**
     * Adds csv export service definitions according.
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function loadCSVServices(array $config, ContainerBuilder $container)
    {
        foreach ($config as $id => $options) {
            $exportId = sprintf('sygefor_core.batch.csv.%s', $id);
            $decorator = new DefinitionDecorator('sygefor_core.batch.csv');
            $container
                ->setDefinition($exportId, $decorator)
                ->addMethodCall('setTargetClass', array($options['class']))
                ->addMethodCall('setOptions', array($options))
                ->setTags(array('sygefor_core.batch_operation_provider' => array()));
        }
    }

    /**
     * Adds csv export service definitions according.
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function loadConvertTypeServices(array $config, ContainerBuilder $container)
    {
        foreach ($config as $id => $options) {
            $exportId = sprintf('sygefor_core.batch.convert_type.%s', $id);
            $decorator = new DefinitionDecorator('sygefor_core.batch.convert_type');
            $container
                ->setDefinition($exportId, $decorator)
                ->addMethodCall('setTargetClass', array($options['class']))
                ->setTags(array('sygefor_core.batch_operation_provider' => array()));
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function loadPubliPostServices(array $config, ContainerBuilder $container)
    {
        foreach ($config as $id => $options) {
            $exportId = sprintf('sygefor_core.batch.publipost.%s', $id);
            $decorator = new DefinitionDecorator('sygefor_core.batch.publipost');
            $container
                ->setDefinition($exportId, $decorator)
                ->addMethodCall('setTargetClass', array($options['class']))
                ->addMethodCall('setOptions', array($options))
                ->setTags(array('sygefor_core.batch_operation_provider' => array()));
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function loadHumanReadablePropertyAccessor(array $config, ContainerBuilder $container)
    {
        $paDefinition = $container->getDefinition('sygefor_core.human_readable_property_accessor_factory');
        $paDefinition->addMethodCall('setTermCatalog', array($config));
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function loadPDFServices(array $config, ContainerBuilder $container)
    {
        foreach ($config as $id => $options) {
            $exportId = sprintf('sygefor_core.batch.pdf.%s', $id);
            $decorator = new DefinitionDecorator('sygefor_core.batch.pdf');
            $container
                ->setDefinition($exportId, $decorator)
                ->addMethodCall('setEntityKey', array($options['key'] ? $options['key'] : $id))
                ->addMethodCall('setTargetClass', array($options['class']))
                ->addMethodCall('setDefaultTemplate', array($options['template']))
                ->addMethodCall('setTemplates', array($options['templates']))
                ->addMethodCall('setFilename', array($options['filename']))
                ->addMethodCall('setTemplateDiscriminator', array($options['templateDiscriminator']))
                ->setTags(array('sygefor_core.batch_operation_provider' => array()));
        }
    }
}
