<?php

namespace Sygefor\Bundle\ApiBundle\Security;

use FOS\OAuthServerBundle\Storage\OAuthStorage;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OAuthTokenGenerator.
 */
class OAuthTokenGenerator
{
    /**
     * @var OAuth2
     */
    private $server;
    /**
     * @var OAuthStorage
     */
    private $storage;
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param OAuth2       $server
     * @param OAuthStorage $storage
     * @param Serializer   $serializer
     */
    function __construct(OAuth2 $server, OAuthStorage $storage, Serializer $serializer)
    {
        $this->server     = $server;
        $this->storage    = $storage;
        $this->serializer = $serializer;
    }

    /**
     * @param $user
     * @param $clientId
     * @param string $clientSecret
     * @param array  $data
     *
     * @throws OAuth2ServerException
     *
     * @return array
     */
    public function generateToken($user, $clientId, $clientSecret = '', $data = array())
    {
        $client        = $this->getClient($clientId, $clientSecret);
        $token         = $this->server->createAccessToken($client, $user, null);
        $userData      = (array) json_decode($this->serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('api', 'api.token'))));
        $token['data'] = array_merge($userData, $data);

        return $token;
    }

    /**
     * @param $user
     * @param $clientId
     * @param string $clientSecret
     * @param array  $data
     *
     * @return Response
     */
    public function generateTokenResponse($user, $clientId, $clientSecret = '', $data = array())
    {
        $token = $this->generateToken($user, $clientId, $clientSecret, $data);

        return new Response(json_encode($token), 200, array('Content-Type' => 'application/json', 'Cache-Control' => 'no-store', 'Pragma' => 'no-cache'));
    }

    /**
     * @param $clientId
     * @param $clientSecret
     *
     * @throws OAuth2ServerException
     *
     * @return \FOS\OAuthServerBundle\Model\ClientInterface
     */
    protected function getClient($clientId, $clientSecret)
    {
        $client = $this->storage->getClient($clientId);
        if ( ! $client) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_CLIENT, 'The client credentials are invalid');
        }
        if ( ! $client->getPublic() && $this->storage->checkClientCredentials($client, $clientSecret) === false) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_CLIENT, 'The client credentials are invalid');
        }

        return $client;
    }

}
