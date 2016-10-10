<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 14/03/14
 * Time: 16:52.
 */
namespace Sygefor\Bundle\CoreBundle\AccessRight;

use Sygefor\Bundle\CoreBundle\Entity\User\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class AccessRightRegistry.
 */
class AccessRightRegistry
{
    /**
     * @var array
     */
    private $rights;

    /**
     * @var array
     */
    private $groups;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * class constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->rights = array();
        $this->groups = array();
        $securityContext = null;
    }

    /**
     * Add right.
     *
     * @param $id
     * @param AbstractAccessRight $accessRight
     * @param string $group
     */
    public function addAccessRight($id, AbstractAccessRight $accessRight, $group = 'Misc')
    {
        $accessRight->setId($id);
        $this->rights[$id] = $accessRight;
        if (!isset($this->groups[$group])) {
            $this->groups[$group] = array();
        }
        $this->groups[$group][] = $id;
    }

    /**
     * @param $id
     *
     * @return null|AccessRightInterface
     */
    public function getAccessRightById($id)
    {
        return isset($this->rights[$id]) ? $this->rights[$id] : null;
    }

    /**
     * Check if a user and not just a group have a special right.
     *
     * @param $id
     * @param User $user
     *
     * @return bool
     */
    public function hasAccessRight($id, User $user = null)
    {
        if ($this->securityContext === null) {
            $this->securityContext = $this->container->get('security.context');
        }

        if ($user === null) {
            $user = $this->securityContext->getToken()->getUser();
        }

        if (!($user instanceof User)) {
            return false;
        }

        $userAccessRights = $user->getAccessRights();
        $userRoles = $user->getRoles();

        return in_array($id, $userAccessRights, true) || in_array('ROLE_ADMIN', $userRoles, true);
    }

    /**
     * returns known rigths.
     *
     * @return array
     */
    public function getAccessRights()
    {
        return $this->rights;
    }

    /**
     * returns known groups.
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param array $rights
     */
    public function setRights($rights)
    {
        $this->rights = $rights;
    }

    /**
     * @return array
     */
    public function getRights()
    {
        return $this->rights;
    }

    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     */
    public function setSecurityContext($securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContextInterface
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }
}
