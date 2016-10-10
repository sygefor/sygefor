<?php
namespace Sygefor\Bundle\ActivityReportBundle\EventListener;


use Sygefor\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Routing\Router;

/**
 * Class ConfigureMenuListener
 */
class ConfigureMenuListener
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    private $securityContext;

    /**
     * @var Router
     */
    private $router;

    /**
     * Construct
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
            $menu->addChild('report', array(
                'label' => 'Bilan',
                'icon'  => 'bar-chart-o',
                'uri' => $this->router->generate('core.index')  . '#/report/training/summaries'
              ));
        }
        catch(AuthenticationCredentialsNotFoundException $e) {

        }
    }
}
