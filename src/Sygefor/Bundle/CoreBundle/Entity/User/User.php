<?php

namespace Sygefor\Bundle\CoreBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User.
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Sygefor\Bundle\CoreBundle\Entity\User\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization", inversedBy="users", cascade={"persist", "merge"})
     * @Assert\NotNull()
     */
    protected $organization;

    /**
     * @var string
     * @ORM\Column(name="access_rights", type="simple_array", nullable=true)
     */
    protected $accessRights;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->accessRights = array();
        $this->enabled      = true;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return mixed
     */
    public function getAccessRights()
    {
        return $this->accessRights;
    }

    /**
     * @param mixed $accessRights
     */
    public function setAccessRights($accessRights)
    {
        $this->accessRights = $accessRights ? $accessRights : array();
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    /**
     * Override default method.
     *
     * @param string $emailCanonical
     *
     * @return $this|\FOS\CoreBundle\Model\UserInterface
     */
    public function setEmailCanonical($emailCanonical)
    {
        return parent::setEmailCanonical(strval(uniqid()) . $emailCanonical);
    }
}
