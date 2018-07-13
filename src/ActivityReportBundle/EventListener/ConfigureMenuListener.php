<?php

namespace ActivityReportBundle\EventListener;

use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;
use Sygefor\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * Class ConfigureMenuListener.
 */
class ConfigureMenuListener
{
    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @var Router
     */
    private $router;

    /**
     * Construct.
     */
    public function __construct(SecurityContext $securityContext, Router $router)
    {
        $this->securityContext = $securityContext;
        $this->router = $router;
    }

    /**
     * @param $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        try {
            if ($this->securityContext->isGranted('BALANCE', 'Sygefor\\Bundle\\UserBundle\\Entity\\User')) {
                $menu->addChild('report', array(
                    'label' => 'Bilan',
                    'icon' => 'bar-chart-o',
                    'uri' => $this->router->generate('core.index').'#/report/training/summaries',
                ));
            }
        } catch (AuthenticationCredentialsNotFoundException $e) {
        }
    }
}
