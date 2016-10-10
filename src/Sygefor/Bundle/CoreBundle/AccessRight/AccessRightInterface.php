<?php

/**
 * Auteur: Blaise de Carné - blaise@concretis.com.
 */
namespace Sygefor\Bundle\CoreBundle\AccessRight;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Interface AccessRightInterface.
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
     *
     * @return bool
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
     *
     * @return bool
     */
    public function supportsAttribute($attribute);
}
