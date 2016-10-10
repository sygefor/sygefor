<?php

/**
 * Auteur: Blaise de Carné - blaise@concretis.com.
 */
namespace Sygefor\Bundle\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Util\MenuManipulator;
use Sygefor\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MenuBuilder.
 */
class MenuBuilder extends ContainerAware
{
    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param Request $request
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        /*$menu->addChild('home', array(
            'label' => 'Accueil',
            'route' => 'core.index'
        ));*/

        $menu->addChild('administration', array(
            'label' => 'Administration',
            'icon' => 'gear',
        ));

        $this->container->get('event_dispatcher')->dispatch(ConfigureMenuEvent::CONFIGURE, new ConfigureMenuEvent($this->factory, $menu));
        $this->container->get('event_dispatcher')->dispatch(ConfigureMenuEvent::ALTER, new ConfigureMenuEvent($this->factory, $menu));

        if ($menu->getChild('administration')->count() === 0) {
            $menu->removeChild('administration');
        }
        else {
            $manipulator = new MenuManipulator();
            $item = $menu->getChild('administration');
            $manipulator->moveToLastPosition($item);
        }

        return $menu;
    }

}
