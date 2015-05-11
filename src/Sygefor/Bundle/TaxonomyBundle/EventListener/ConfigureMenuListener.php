<?php
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 */
namespace Sygefor\Bundle\TaxonomyBundle\EventListener;

use Sygefor\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class ConfigureMenuListener
 * @package Sygefor\Bundle\TaxonomyBundle\EventListener
 */
class ConfigureMenuListener
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    private $securityContext;

    /**
     * Construct
     */
    public function __construct(SecurityContext $securityContext) {
        $this->securityContext = $securityContext;
    }

    /**
     * @param $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $adminMenu = $menu->getChild('administration');

        try {
            if($this->securityContext->isGranted('VIEW', 'SygeforTaxonomyBundle:AbstractTerm') || $this->securityContext->isGranted('VIEW', 'Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface') ) {

                $adminMenu->addChild('taxonomy', array(
                        'label' => 'Vocabulaires',
                        'route' => 'taxonomy.index'
                    ));
            }

        } catch(AuthenticationCredentialsNotFoundException $e) {

        }
    }
}
