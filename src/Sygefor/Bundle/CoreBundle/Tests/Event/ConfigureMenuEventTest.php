<?php
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 */

namespace Sygefor\Bundle\CoreBundle\Tests\Event;

use Sygefor\Bundle\CoreBundle\Event\ConfigureMenuEvent;

/**
 * Class ConfigureMenuEventTest
 * @package Sygefor\Bundle\CoreBundle\Tests\Event
 */
class ConfigureMenuEventTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $factory = $this->getMock('Knp\Menu\FactoryInterface');
        $menu = $this->getMock('Knp\Menu\ItemInterface');
        $event = new ConfigureMenuEvent($factory, $menu);
        $this->assertEquals($factory, $event->getFactory());
        $this->assertEquals($menu, $event->getMenu());
    }
}
