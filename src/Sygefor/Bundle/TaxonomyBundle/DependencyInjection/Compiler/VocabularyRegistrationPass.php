<?php

namespace Sygefor\Bundle\TaxonomyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class VocabularyRegistrationPass
 * @package Sygefor\Bundle\TaxonomyBundle\DependencyInjection\Compiler
 */
class VocabularyRegistrationPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sygefor_taxonomy.vocabulary_registry')) {
            return;
        }

        $definition = $container->getDefinition('sygefor_taxonomy.vocabulary_registry');

        $vocabularySevices = $container->findTaggedServiceIds('sygefor_taxonomy.vocabulary_provider');

        foreach ($vocabularySevices as $id => $tagAttributes) {
            //checking class
            $class = $container->getDefinition($id)->getClass();
            if (!$class||!$this->isVocabularyProviderImplementation($class)) {
                throw new \InvalidArgumentException(sprintf('Vocabulary Registration : %s must implement NationalVocabularyInterface',$class));
            }
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addVocabulary', array(new Reference($id), $id, $attributes["group"])
                );
            }
        }
    }

    /**
     * Returns whether the class implements VocabularyInterface.
     *
     * @param string $class
     * @return boolean
     */
    private function isVocabularyProviderImplementation($class)
    {
        $refl = new \ReflectionClass($class);
        return $refl->implementsInterface('Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface');
    }
}
