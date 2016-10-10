<?php

namespace Sygefor\Bundle\TraineeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class TraineeRepository.
 *
 * @see http://symfony.com/fr/doc/current/cookbook/security/entity_provider.html
 */
class TraineeRepository extends EntityRepository implements UserProviderInterface
{
    /**
     * @param string $email
     *
     * @throws UsernameNotFoundException
     *
     * @return mixed
     */
    public function loadUserByUsername($email)
    {
        $q = $this
          ->createQueryBuilder('t')
          ->where('t.email = :email')
          ->setParameter('email', $email)
          ->getQuery();

        try {
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf(
                'Unable to find an active trainee identified by "%s".',
                $email
            );
            throw new UsernameNotFoundException($message, 0, $e);
        }

        return $user;
    }

    /**
     * @param UserInterface $user
     *
     * @throws UnsupportedUserException
     *
     * @return object
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if ( ! $this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($user->getId());
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

    /**
     * Generate a password.
     *
     * @param int $length
     *
     * @return string
     */
    public static function generatePassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; ++$i) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }
}
