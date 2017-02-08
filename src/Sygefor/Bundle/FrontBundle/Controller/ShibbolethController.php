<?php

namespace Sygefor\Bundle\FrontBundle\Controller;

use Sygefor\Bundle\ApiBundle\Controller\ShibbolethController as BaseShibbolethController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/api")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ShibbolethController extends BaseShibbolethController
{
    /**
     * Prepare register data based on shibboleth attributes.
     */
    protected function prepareRegisterData($attrs)
    {
        return parent::prepareRegisterData($attrs);
    }
}
