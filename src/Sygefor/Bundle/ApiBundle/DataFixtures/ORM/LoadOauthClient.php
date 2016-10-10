<?php

namespace Sygefor\Bundle\ApiBundle\DataFixtures\ORM;

use Sygefor\Bundle\ApiBundle\Entity\Client;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

/**
 * Class LoadOauthClient.
 */
class LoadOauthClient extends AbstractTermLoad
{
    static $class = Client::class;

    public function getTerms()
    {
        return array(
            array(
                'randomId' => '5cn6gjovjzks48ckkowkgko8owk8o8ccwow8o4w0c84c40kwsk',
                'redirectUris' => array(
                    'http://localhost:3000',
                    'https://' . $this->container->getParameter('front_host'),
                ),
                'allowedGrantTypes' => array(
                    'password',
                    'refresh_token',
                ),
                'public' => true
            )
        );
    }
}
