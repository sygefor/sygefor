<?php
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 */
namespace Sygefor\Bundle\UserBundle\EventListener;

use Sygefor\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class ConfigureMenuListener
 * @package Sygefor\Bundle\UserBundle\EventListener
 */
class ConfigureMenuListener
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    private $securityContext;

    /**
     * Construct
     */
    public function __construct(SecurityContext $securityContext) {
        $this->securityContext = $securityContext;
    }

    /**
     * @param $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $adminMenu = $menu->getChild('administration');

        try {
            if($this->securityContext->isGranted('VIEW', 'SygeforUserBundle:User')) {
                $adminMenu->addChild('users', array(
                        'label' => 'Utilisateurs',
                        'route' => 'user.index'
                    ));
            }
        } catch(AuthenticationCredentialsNotFoundException $e) {

        }
    }
}
