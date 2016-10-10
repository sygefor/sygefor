<?php

namespace Sygefor\Bundle\FrontBundle\EventListener;


use FOS\OAuthServerBundle\Entity\ClientManager;
use KULeuven\ShibbolethBundle\Security\ShibbolethUserToken;
use Sygefor\Bundle\ApiBundle\Security\OAuthTokenGenerator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class ShibbolethAuthenticationSuccessListener.
 */
class ShibbolethAuthenticationSuccessListener
{
    /** @var Container  */
    private $container;

    /** @var ClientManager  */
    private $clientManager;

    /** @var OAuthTokenGenerator  */
    private $authTokenGenerator;

    /**
     * @param ClientManager $clientManager
     */
    public function __construct(Container $container, ClientManager $clientManager, OAuthTokenGenerator $authTokenGenerator)
    {
        $this->container = $container;
        $this->clientManager = $clientManager;
        $this->authTokenGenerator = $authTokenGenerator;
    }

    public function onAuthenticationSuccess(InteractiveLoginEvent $loginEvent)
    {
        $token      = $loginEvent->getAuthenticationToken();
        if ($token && $token instanceof ShibbolethUserToken && !is_string($token->getUser())) {
            $user = $token->getUser();
            $client = $this->clientManager->findClientByPublicId("1_5cn6gjovjzks48ckkowkgko8owk8o8ccwow8o4w0c84c40kwsk");

            $token = $this->authTokenGenerator->generateTokenResponse($user, $client->getPublicId(), $client->getSecret());

            return $loginEvent;
        }

        return $loginEvent;
    }
}
