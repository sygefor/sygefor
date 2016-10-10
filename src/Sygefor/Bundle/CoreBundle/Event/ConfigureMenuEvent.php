<?php

/**
 * Auteur: Blaise de Carné - blaise@concretis.com.
 */
namespace Sygefor\Bundle\CoreBundle\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ConfigureMenuEvent.
 */
class ConfigureMenuEvent extends Event
{
    const CONFIGURE = 'sygefor_core.menu_configure';
    const ALTER     = 'sygefor_core.menu_alter';

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;
    /**
     * @var \Knp\Menu\ItemInterface
     */
    private $menu;

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param \Knp\Menu\ItemInterface    $menu
     */
    public function __construct(FactoryInterface $factory, ItemInterface $menu)
    {
        $this->factory = $factory;
        $this->menu    = $menu;
    }

    /**
     * @return \Knp\Menu\FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function getMenu()
    {
        return $this->menu;
    }
}
