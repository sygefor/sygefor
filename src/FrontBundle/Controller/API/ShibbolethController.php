<?php

namespace FrontBundle\Controller\API;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sygefor\Bundle\ApiBundle\Controller\ShibbolethController as BaseShibbolethController;

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
        $em = $this->container->get('doctrine')->getManager();

        $data = array(
            'email' => @$attrs['mail'],
            'firstName' => @$attrs['firstName'],
            'lastName' => @$attrs['lastName'],
            'unityCode' => @$attrs['unityCode'],
        );

        return $data;
    }
}
