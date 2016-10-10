<?php

/**
 * Auteur: Blaise de Carné - blaise@concretis.com.
 */
namespace Sygefor\Bundle\CoreBundle\Tests\Menu;

use Knp\Menu\MenuFactory;
use Sygefor\Bundle\CoreBundle\Menu\MenuBuilder;

require_once dirname(__DIR__) . '/../../../../../app/AppKernel.php';

/**
 * Class MenuBuilderTest.
 */
class MenuBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     */
    public function setUp()
    {
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();
        parent::setUp();
    }

    /**
     * testCreateMainMenu.
     */
    public function testCreateMainMenu()
    {
        $factory     = new MenuFactory();
        $request     = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $menuBuilder = new MenuBuilder($factory);
        $menuBuilder->setContainer($this->container);
        $menu = $menuBuilder->createMainMenu($request);

        $this->assertInstanceOf('Knp\Menu\MenuItem', $menu);
        //$this->assertInstanceOf('Knp\Menu\MenuItem', $menu->getChild('home'));
        //$this->assertInstanceOf('Knp\Menu\MenuItem', $menu->getChild('administration'));
    }
}
