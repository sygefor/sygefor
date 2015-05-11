<?php
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 */
namespace Sygefor\Bundle\UserBundle\AccessRight;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Interface AccessRightInterface
 * @package Sygefor\Bundle\UserBundle\AccessRight
 */
interface AccessRightInterface
{
    /**
     * @return string
     */
    public function getLabel();

    /**
     * Checks if the access right supports the given class.
     *
     * @param string
     * @return Boolean
     */
    public function supportsClass($class);

    /**
     * Returns the vote for the given parameters.
     */
    public function isGranted(TokenInterface $token, $object = null, $attribute);

    /**
     * @param int $id
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * Checks if the access right supports the given attribute.
     *
     * @param string $attribute
     * @return Boolean
     */
    public function supportsAttribute($attribute);

}
