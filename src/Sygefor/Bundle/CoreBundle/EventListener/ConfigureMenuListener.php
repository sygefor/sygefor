<?php

namespace Sygefor\Bundle\CoreBundle\EventListener;

use Sygefor\Bundle\CoreBundle\Event\ConfigureMenuEvent;
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
     * Construct.
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @param $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu      = $event->getMenu();
        $adminMenu = $menu->getChild('administration');

        try {
            if ($this->securityContext->isGranted('VIEW', 'Sygefor\Bundle\CoreBundle\Entity\Organization')) {
                $adminMenu->addChild('organizations', array(
                        'label' => 'Centres',
                        'route' => 'organization.index',
                    )
                );
            }

            if ($this->securityContext->isGranted('VIEW', 'Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm') || $this->securityContext->isGranted('VIEW', 'Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface') ) {
                $adminMenu->addChild('taxonomy', array(
                        'label' => 'Vocabulaires',
                        'route' => 'taxonomy.index',
                    )
                );
            }


            if ($this->securityContext->isGranted('VIEW', 'Sygefor\Bundle\CoreBundle\Entity\User\User')) {
                $adminMenu->addChild('users', array(
                    'label' => 'Utilisateurs',
                    'route' => 'user.index',
                ));
            }
        }
        catch (AuthenticationCredentialsNotFoundException $e) {

        }
    }
}
