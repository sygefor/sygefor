<?php

/**
 * Auteur: Blaise de Carné - blaise@concretis.com.
 */
namespace Sygefor\Bundle\TrainingBundle\EventListener;

use Sygefor\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Sygefor\Bundle\TrainingBundle\Registry\TrainingTypeRegistry;
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
     * @var TrainingTypeRegistry
     */
    private $registry;

    /**
     * Construct.
     */
    public function __construct(SecurityContext $securityContext, Router $router, TrainingTypeRegistry $registry) {
        $this->securityContext = $securityContext;
        $this->router          = $router;
        $this->registry        = $registry;
    }

    /**
     * @param $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        try {
            if($this->securityContext->isGranted('VIEW', 'SygeforTrainingBundle:Training\AbstractTraining')) {
                $item = $menu->addChild('trainings', array(
                    'label' => 'Événements',
                    'icon'  => 'calendar',
                    'uri'   => $this->router->generate('core.index') . '#/training',
                ));

                foreach($this->registry->getTypes() as $key => $type) {
                    $item->addChild('trainings.' . $key, array(
                        'label' => $type['label'],
                        'uri'   => $this->router->generate('core.index') . '#/training?type=' . $key,
                    ));
                }

                $item->addChild('sessions', array(
                    'label' => 'Toutes les sessions',
                    'uri'   => $this->router->generate('core.index') . '#/training/session',
                ))->setAttribute('divider_prepend', true);

            }
        } catch(AuthenticationCredentialsNotFoundException $e) {

        }
    }
}
