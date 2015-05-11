<?php
namespace Sygefor\Bundle\ApiBundle\HttpKernel\EventSubscriber;

use FOS\OAuthServerBundle\Controller\TokenController;
use FOS\OAuthServerBundle\Entity\ClientManager;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use OAuth2\OAuth2;
use OAuth2\OAuth2AuthenticateException;
use OAuth2\OAuth2ServerException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Class OauthKernelEventSubscriber
 * @package Sygefor\Bundle\ApiBundle\HttpKernel\EventSubscriber
 */
class OauthKernelEventSubscriber implements EventSubscriberInterface
{
    /** @var ClientManager  */
    private $clientManager;

    /** @var OAuth2  */
    private $serverService;

    /** @var Serializer  */
    private $serializer;

    /**
     * @param ClientManager $clientManager
     */
    public function __construct(ClientManager $clientManager, OAuth2 $serverService, Serializer $serializer) {
        $this->clientManager = $clientManager;
        $this->serverService = $serverService;
        $this->serializer = $serializer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER  => 'onKernelController',
            KernelEvents::RESPONSE  => 'onKernelResponse'
        );
    }

    /**
     * Handle public client
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = current($event->getController());
        $request = $event->getRequest();

        if($controller instanceof TokenController) {
            $clientId =  $request->get('client_id');
            $grantType =  $request->get('grant_type');
            $client = $this->clientManager->findClientByPublicId($clientId);
            $authorized = in_array($grantType, array('password', 'refresh_token'));
            if($authorized && $client && $client->getPublic()) {
                $prop = $request->getMethod() === 'POST' ? "request" : "query";
                $request->$prop->set('client_secret', $client->getSecret());
            }
        }

        // HACK
        // @todo : remove it in production
        if($request->server->has('PHP_AUTH_USER')) {
            $request->server->remove('PHP_AUTH_USER');
        }
    }

    /**
     * Add user profile to token controller response + disabled user
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $controller = $event->getRequest()->get('_controller');
        // check provenance
        if($controller == 'fos_oauth_server.controller.token:tokenAction' && $event->getResponse()->getStatusCode() == 200) {
            // decode content
            $data = json_decode($event->getResponse()->getContent(), true);
            // get the token
            $tokenString = $data['access_token'];
            // verify it
            if ($accessToken = $this->serverService->verifyAccessToken($tokenString)) {
                // get the user
                $user = $accessToken->getUser();

                // disallow disabled user
                if($user instanceof AdvancedUserInterface && !$user->isEnabled()) {
                    $e = new OAuth2ServerException(403, "disabled_user");
                    $event->setResponse($e->getHttpResponse());
                } else {
                    // serialize user
                    $serialized = $this->serializer->serialize($user, "json", SerializationContext::create()->setGroups(array('api', 'api.token')));
                    // add profile to data, re-encode et update the response content
                    $data['data'] = json_decode($serialized);
                    $event->getResponse()->setContent(json_encode($data));
                }
            }
        }
    }
}
