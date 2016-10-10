<?php

namespace Sygefor\Bundle\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/api")
 */
class ProxyController extends Controller
{
    /**
     * @Route("/proxy.html", name="api.xdomain.proxy")
     * @Template()
     */
    public function proxyAction(Request $request)
    {
        $front_url = $this->container->getParameter('front_url');
        $front_url = preg_replace('/#$/', '', $front_url);
        preg_match('%^((?:http://|https://)[A-Za-z0-9.-]+(?!.*\|\w*$)(?::\d+)?)(.*)%sim', $front_url, $matches);
        $host = $matches[1];
        $path = $matches[2];

        return array(
          'host' => $host,
          'path' => $path,
        );
    }
}
