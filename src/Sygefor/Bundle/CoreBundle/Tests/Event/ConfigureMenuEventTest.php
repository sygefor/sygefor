<?php

/**
 * Auteur: Blaise de Carné - blaise@concretis.com.
 */
namespace Sygefor\Bundle\CoreBundle\Tests\Event;

use Sygefor\Bundle\CoreBundle\Event\ConfigureMenuEvent;

/**
 * Class ConfigureMenuEventTest.
 */
class ConfigureMenuEventTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $factory = $this->getMock('Knp\Menu\FactoryInterface');
        $menu    = $this->getMock('Knp\Menu\ItemInterface');
        $event   = new ConfigureMenuEvent($factory, $menu);
        $this->assertSame($factory, $event->getFactory());
        $this->assertSame($menu, $event->getMenu());
    }
}
