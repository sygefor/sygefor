<?php
namespace Sygefor\Bundle\ApiBundle\Security;

use KULeuven\ShibbolethBundle\Security\ShibbolethUserProviderInterface;
use KULeuven\ShibbolethBundle\Security\ShibbolethUserToken;
use KULeuven\ShibbolethBundle\Service\Shibboleth;
use Sygefor\Bundle\ApiBundle\Entity\ShibbolethAttributes;
use Sygefor\Bundle\ApiBundle\Entity\ShibbolethRegisterRequest;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class ShibbolethUserProvider implements ShibbolethUserProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TraineeRepository
     */
    private $repository;

    /**
     * @inheritdoc
     */
    function __construct(ContainerInterface $container, TraineeRepository $repository)
    {
        $this->container = $container;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function loadUserByUsername($username)
    {
        // force not found to use the createUser method
        throw new UsernameNotFoundException();
    }

    /**
     * @inheritdoc
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->repository->refreshUser($user);
    }

    /**
     * @inheritdoc
     */
    public function supportsClass($class)
    {
        return $this->repository->supportsClass($class);
    }

    /**
     * If no user was found based on persistentId, try to find it by email
     */
    public function createUser(ShibbolethUserToken $token)
    {
        $em = $this->container->get('doctrine')->getManager();
        $email = $token->getAttribute('mail');

        $identityProvider = $token->getAttribute('identityProvider');
        $persistentId = $token->getAttribute('persistent_id');
        $targetedId = $token->getAttribute('targeted_id');
        $eppn = $token->getAttribute('eppn');

        // try to find a proper persistent id
        $shibbolethId = $persistentId ? $persistentId : $targetedId;

        // else, build a custom one with eppn
        if(!$shibbolethId && $identityProvider && $eppn) {
            $shibbolethId = $identityProvider.'!'.$eppn;
        }

        // else, set it to 1
        if(!$shibbolethId) {
            $shibbolethId = 1;
        }

        // try to find the user by email, and then by persistent id
        $user = $this->repository->findOneByEmail($email);
        if(!$user && $shibbolethId != 1) {
            $user = $this->repository->findOneByShibbolethPersistentId($shibbolethId);
        }

        if($user) {
            /*if($user->getShibbolethPersistentId() && $persistentId != $user->getShibbolethPersistentId()) {
                throw new UsernameNotFoundException("The email belongs to another shibboleth account.");
            }*/
            // set the new persistent id
            $user->setShibbolethPersistentId($shibbolethId);
            // set the mail
            // $user->setEmail($email);
            $em->flush();
            return $user;
        }
        return null;
    }
}
