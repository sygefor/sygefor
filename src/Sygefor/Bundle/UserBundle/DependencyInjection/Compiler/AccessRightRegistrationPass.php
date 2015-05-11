<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 14/03/14
 * Time: 16:31
 */

namespace Sygefor\Bundle\UserBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AccessRightRegistrationPass implements CompilerPassInterface
{
    /**
     * Process the compiler pass
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sygefor_user.access_right_registry')) {
            return;
        }

        $definition = $container->getDefinition('sygefor_user.access_right_registry');

        $rightsRegistrants = $container->findTaggedServiceIds('sygefor_user.right_provider');

        foreach ($rightsRegistrants as $id => $tagAttributes) {
            foreach($tagAttributes as $attributes) {
                //checking class
                $class = $container->getDefinition($id)->getClass();
                if (!$class||!$this->isAccessRightImplementation($class)) {
                    throw new \InvalidArgumentException(sprintf('Access Right Registration : %s must implement AccessRightInterface',$class));
                }
                $definition->addMethodCall(
                    'addAccessRight',array($id, new Reference($id), isset($attributes['group']) ? $attributes['group'] : 'Misc')
                );
            }
        }
    }

    /**
     * Returns whether the class implements AccessRightProviderInterface.
     *
     * @param string $class
     * @return boolean
     */
    private function isAccessRightImplementation($class)
    {
        $refl = new \ReflectionClass($class);
        return $refl->implementsInterface('Sygefor\Bundle\UserBundle\AccessRight\AccessRightInterface');
    }
}
