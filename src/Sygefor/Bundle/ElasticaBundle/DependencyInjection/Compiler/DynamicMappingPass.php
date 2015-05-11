<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 01/09/14
 * Time: 10:23
 */

namespace Sygefor\Bundle\ElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MappingProviderPass
 * @package Sygefor\Bundle\ElasticaUpdateBundle\DependencyInjection\Compiler
 */
class DynamicMappingPass implements CompilerPassInterface
{
    /**
     * Process the compiler pass
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $typeConfigs = array();
        $elasticaConfig = $container->getExtensionConfig('fos_elastica');

        // populate the $typeConfigs array
        foreach($elasticaConfig[0]['indexes'] as $index => $indexConfig) {
            foreach($indexConfig['types'] as $type => $typeConfig) {
                $typeConfigs[$index][$type] = $typeConfig['mappings'];
            }
        }

        // extract current config source
        $sourceConfigs = $container->getDefinition('fos_elastica.config_source.container')->getArgument(0);

        foreach($typeConfigs as $index => $types) {
            foreach($types as $type => $fields) {

                // if the type name begin with _, it's a abstract type
                if(substr($type,0,1) == "_") {
                    // remove from the config source
                    unset($sourceConfigs[$index]['types'][$type]);
                    continue;
                }

                // rework the config
                $typeConfigs[$index][$type] = $this->extendTypeConfig($fields, $typeConfigs[$index]);
                $mapping = $typeConfigs[$index][$type];

                // replace the config in indexConfigs
                $sourceConfigs[$index]['types'][$type]['mapping']['properties'] = $mapping;

                // if any, replace the persister 3thd argument
                $persisterId = "fos_elastica.object_persister.$index.$type";
                if($container->hasDefinition($persisterId)) {
                    $container->getDefinition($persisterId)->replaceArgument(3, $mapping);
                }
            }
        }

        // replace config source
        $container->getDefinition('fos_elastica.config_source.container')->replaceArgument(0, $sourceConfigs);

//        var_dump($typeConfigs);
//        die;
    }

    /**
     * @param $fields
     * @param $typeConfigs
     * @param array $exclude
     * @return array
     */
    private function extendTypeConfig($fields, $typeConfigs) {
        $includedFields = array();
        foreach($fields as $field => $config) {
            if($field == "_include" ) {
                $exclude = array();
                if(is_array($config)) {
                    $exclude = (array)$config['exclude'];
                    $config = $config['type'];
                }
                $includedFields = array_merge($includedFields, $this->extractMapping($config, $typeConfigs, $exclude));
                unset($fields[$field]);
            } elseif(is_array($config)) {
                $fields[$field] = $this->extendTypeConfig($config, $typeConfigs);
            }
        }
        if($includedFields) {
            $fields = array_merge($includedFields, $fields);
        }
        return $fields;
    }

    /**
     * @param $path
     * @param $typeConfigs
     * @param array $exclude
     * @return array
     */
    private function extractMapping($path, $typeConfigs, $exclude = array()) {
        $parts = explode(".", $path);
        $fields = $typeConfigs;
        foreach($parts as $part) {
            $fields = $fields[$part];
        }
        // exclude some fields
        foreach($exclude as $field) {
            unset($fields[$field]);
        }
        // exclude private
        foreach($fields as $key => $field) {
            if(!empty($field['_private'])) {
                unset($fields[$key]);
            }
        }
        return $this->extendTypeConfig($fields, $typeConfigs);
    }

}
