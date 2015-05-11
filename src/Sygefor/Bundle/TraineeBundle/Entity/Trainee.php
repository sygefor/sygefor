<?php

namespace Sygefor\Bundle\TraineeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\CoordinatesTrait;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Trainee
 *
 * @ORM\Table(name="trainee", uniqueConstraints={@ORM\UniqueConstraint(name="emailUnique", columns={"email"})}))
 * @ORM\Entity(repositoryClass="Sygefor\Bundle\TraineeBundle\Entity\TraineeRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"email"}, message="Cette adresse email est déjà utilisée.")
 * @ORM\HasLifecycleCallbacks()
 */
class Trainee implements UserInterface, \Serializable, SerializedAccessRights, AdvancedUserInterface
{
    use ORMBehaviors\Timestampable\Timestampable;
    use PersonTrait;
    use CoordinatesTrait;
    use ProfessionalSituationTrait;

    /**
     * @var integer id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string Organization
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization")
     * @Assert\NotNull(message="Vous devez renseigner une URFIST de rattachement.")
     * @Serializer\Groups({"trainee", "session", "api.profile"})})
     */
    protected $organization;

    /**
     * @ORM\OneToMany(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Inscription", mappedBy="trainee", cascade={"remove"})
     * @Serializer\Groups({"trainee"})
     */
    protected $inscriptions;

    /**
     *@ORM\OneToMany(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\TraineeDuplicate", mappedBy="traineeSource")
     */
    protected $duplicates;

    /**
     * @ORM\Column(type="string", length=32)
     * @Serializer\Exclude
     */
    private $salt;

    /**
     * string
     * @Serializer\Exclude
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string")
     * @Serializer\Exclude
     */
    private $password;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     * @Serializer\Groups({"trainee"})
     */
    private $isActive;

    /**
     * @ORM\Column(name="shibboleth_persistent_id", type="string", nullable=true)
     * @Serializer\Groups({"api.token", "api.profile"})
     */
    private $shibbolethPersistentId;

    /**
     * @ORM\Column(name="data", type="array", nullable=true)
     * @Serializer\Exclude
     */
    private $data;

    /**
     * @var bool
     * @Serializer\Exclude
     */
    private $sendCredentialsMail = false;

    /**
     * @var mixed
     * @Serializer\Exclude
     * This properties is used to automatically send a activation link to the trainee.
     * true or array of options
     */
    private $sendActivationMail = false;

    /**
     * Construct
     */
    function __construct()
    {
        $this->inscriptions = new ArrayCollection();
        $this->duplicates = new ArrayCollection();
        $this->isActive = true;
        $this->salt = md5(uniqid(null, true));
        $this->password = md5(uniqid(null, true));
        $this->addressType = 0;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function setUsername($username)
    {
        $this->email = $username;
    }

    /**
     * @param mixed $inscriptions
     */
    public function setInscriptions($inscriptions)
    {
        $this->inscriptions = $inscriptions;
    }

    /**
     * @return ArrayCollection
     */
    public function getInscriptions()
    {
        return $this->inscriptions;
    }

    /**
     * @return mixed
     */
    public function getDuplicates()
    {
        return $this->duplicates->filter(function($duplicate) {
            return $duplicate->isIgnored() == false;
        });
    }

    /**
     *
     */
    public function clearDuplicates()
    {
        $this->duplicates->clear();
    }

    /**
     * Return array list
     * @see elastica
     */
    public function getDuplicatesList()
    {
        if(count($this->getDuplicates())) {
            $array = array();
            foreach ($this->getDuplicates() as $duplicate) {
                $array[] = $duplicate->getTraineeTarget()->getId();
            }
            return $array;
        }
        return null;
    }

    public function getIgnoredDuplicates()
    {
        return $this->duplicates->filter(function($duplicate) {
            return $duplicate->isIgnored() == true;
        });
    }

    /**
     * @return ArrayCollection
     */
    public function getAllDuplicates()
    {
        return $this->duplicates;
    }

    /**
     * @return mixed
     */
    public function addDuplicate($duplicate)
    {
        if (!$this->duplicates->contains($duplicate)) {
            return $this->duplicates->add($duplicate);
        }
    }

    /**
     * @param mixed $duplicates
     */
    public function removeDuplicate($duplicate)
    {
        if ($this->duplicates->contains($duplicate)) {
            return $this->duplicates->remove($duplicate);
        }
    }

    /**
     * @param mixed $duplicates
     */
    public function setDuplicates($duplicates)
    {
        $this->duplicate = $duplicates;
    }

    /**
     * @param string $organization
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
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isSendCredentialsMail()
    {
        return $this->sendCredentialsMail;
    }

    /**
     * @param boolean $sendCredentialsMail
     */
    public function setSendCredentialsMail($sendCredentialsMail)
    {
        $this->sendCredentialsMail = $sendCredentialsMail;
    }

    /**
     * @return boolean
     */
    public function getSendActivationMail()
    {
        return $this->sendActivationMail;
    }

    /**
     * @param mixed $sendActivationMail
     */
    public function setSendActivationMail($sendActivationMail)
    {
        $this->sendActivationMail = $sendActivationMail;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return array('ROLE_TRAINEE');
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->id,
            )
        );
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list ($this->id) = unserialize($serialized);
    }

    /**
     * @return mixed
     */
    public function getShibbolethPersistentId()
    {
        return $this->shibbolethPersistentId;
    }

    /**
     * @param mixed $shibbolethPersistentId
     */
    public function setShibbolethPersistentId($shibbolethPersistentId)
    {
        $this->shibbolethPersistentId = $shibbolethPersistentId;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "";
    }

    /**
     * @see Symfony\Component\Security\Core\User\AdvancedUserInterface
     * @return bool
     */
    public function isAccountNonExpired() {
        return true;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AdvancedUserInterface
     * @return bool
     */
    public function isAccountNonLocked() {
        return true;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AdvancedUserInterface
     * @return bool
     */
    public function isCredentialsNonExpired() {
        return true;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AdvancedUserInterface
     * @return bool
     */
    public function isEnabled() {
        return $this->isActive;
    }

    /**
     * @ORM\PreRemove
     */
    public function removeDuplicates(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        /**
         * @var TraineeDuplicate $duplicate
         */
        foreach ($this->getAllDuplicates() as $duplicate) {
            $traineeTarget = $duplicate->getTraineeTarget();
            $traineeSource = $duplicate->getTraineeSource();
            $duplicateTraineeTarget = $traineeTarget->getAllDuplicates();
            if ($duplicateTraineeTarget) {
                $duplicateTargetList = $duplicateTraineeTarget->filter(function ($duplicate) use ($traineeSource) {
                    return $duplicate->getTraineeTarget()->getId() == $traineeSource->getId();
                });

                if (count($duplicateTargetList) > 0) {
                    foreach ($duplicateTargetList as $duplicateToRemove) {
                        /**
                         * @var Trainee $traineeSource2
                         */
                        $traineeSource2 = $duplicateToRemove->getTraineeSource();
                        if ($traineeSource2) {
                            if ($traineeSource2->getAllDuplicates()->contains($duplicateToRemove)) {
                                $em->remove($duplicateToRemove);
                            }
                        }
                        $traineeSource2->updateTimestamps();
                    }
                }
            }
            $em->remove($duplicate);
        }

        $this->clearDuplicates();
        $em->flush();
    }
}
