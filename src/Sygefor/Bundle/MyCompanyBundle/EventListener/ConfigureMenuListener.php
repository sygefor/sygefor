<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/8/16
 * Time: 4:23 PM.
 */
namespace Sygefor\Bundle\MyCompanyBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Knp\Menu\Util\MenuManipulator;
use Sygefor\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class ConfigureMenuListener.
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
     * @var EntityManager
     */
    private $em;

    /**
     * Construct.
     */
    public function __construct(SecurityContext $securityContext, Router $router, EntityManager $em)
    {
        $this->securityContext = $securityContext;
        $this->router          = $router;
        $this->em              = $em;
    }

    /**
     * @param $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        try {
            $menuItems = $this->em->getRepository('SygeforMyCompanyBundle:Term\MenuItem')->findBy(array(), (array('name' => 'DESC')));
            if (!empty($menuItems)) {

                $externalLinksMenuItem = $menu->addChild('external_link', array(
                    'label' => 'Liens externes',
                    'icon' => 'external-link'
                ));

                foreach ($menuItems as $key => $menuItem) {
                    $externalLinksMenuItem->addChild('menuItemTerms' . strval($menuItem->getId()), array(
                        'label' => $menuItem->getName(),
                        'uri' => $menuItem->getLink()
                    ));
                }
            }
        }
        catch(AuthenticationCredentialsNotFoundException $e) {

        }
    }

    /**
     * @param $event
     */
    public function onAlterConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        try {
//            rename menu item example
//            if ($menu->getChild('institutions')) {
//                $menu->getChild('institutions')->setLabel('Unités');
//            }

            $manipulator = new MenuManipulator();
            $item = $menu->getChild('external_link');
            if ($item) {
                $manipulator->moveToLastPosition($item);
            }
        }
        catch(AuthenticationCredentialsNotFoundException $e) {

        }
    }
}
