<?php

namespace Sygefor\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class VocabularyRegistrationPass.
 */
class VocabularyRegistrationPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sygefor_core.vocabulary_registry')) {
            return;
        }

        $definition = $container->getDefinition('sygefor_core.vocabulary_registry');
        $vocabularySevices = $container->findTaggedServiceIds('sygefor_core.vocabulary_provider');
        foreach ($vocabularySevices as $id => $tagAttributes) {
            //checking class
            $class = $container->getDefinition($id)->getClass();
            if (!$class || !$this->isVocabularyProviderImplementation($class)) {
                throw new \InvalidArgumentException(sprintf('Vocabulary Registration : %s must implement VocabularyInterface', $class));
            }
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addVocabulary', array(new Reference($id), $id, $attributes['group'], isset($attributes['label']) ? $attributes['label'] : null)
                );
            }
        }
    }

    /**
     * Returns whether the class implements VocabularyInterface.
     *
     * @param string $class
     *
     * @return bool
     */
    private function isVocabularyProviderImplementation($class)
    {
        $refl = new \ReflectionClass($class);

        return $refl->implementsInterface('Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface');
    }
}
