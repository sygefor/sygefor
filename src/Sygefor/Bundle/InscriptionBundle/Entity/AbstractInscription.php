<?php

namespace Sygefor\Bundle\InscriptionBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait\CoordinatesTrait;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait\ProfessionalSituationTrait;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Sygefor\Bundle\CoreBundle\AccessRight\SerializedAccessRights;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\InscriptionBundle\Form\BaseInscriptionType;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Trainee.
 *
 * @ORM\Table(name="inscription", uniqueConstraints={@UniqueConstraint(name="traineesession_idx", columns={"trainee_id", "session_id"})})
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"trainee", "session"}, message="Cet utilisateur est déjà inscrit à cette session !")
 */
abstract class AbstractInscription implements SerializedAccessRights
{
    use ORMBehaviors\Timestampable\Timestampable;
    use CoordinatesTrait;
    use ProfessionalSituationTrait;

    /**
     * @var int id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee", inversedBy="inscriptions")
     * @ORM\JoinColumn(name="trainee_id", referencedColumnName="id")
     * @Assert\NotNull(message="Vous devez sélectionner un stagiaire.")
     * @Serializer\Groups({"inscription", "session"})
     */
    protected $trainee;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession", inversedBy="inscriptions")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id")
     * @Assert\NotNull()
     * @Serializer\Groups({"inscription", "trainee", "api"})
     */
    protected $session;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\InscriptionBundle\Entity\Term\InscriptionStatus")
     * @Assert\NotNull(message="Vous devez spécifier un status d'inscription.")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $inscriptionStatus;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\InscriptionBundle\Entity\Term\PresenceStatus")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $presenceStatus;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $inscriptionStatusUpdatedAt;

    /**
     * @var bool
     */
    protected $sendInscriptionStatusMail = false;

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
     * @param mixed $inscriptionStatus
     */
    public function setInscriptionStatus($inscriptionStatus)
    {
        $this->inscriptionStatus = $inscriptionStatus;
    }

    /**
     * @return InscriptionStatus
     */
    public function getInscriptionStatus()
    {
        return $this->inscriptionStatus;
    }

    /**
     * @param mixed $presenceStatus
     */
    public function setPresenceStatus($presenceStatus)
    {
        $this->presenceStatus = $presenceStatus;
    }

    /**
     * @return PresenceStatus
     */
    public function getPresenceStatus()
    {
        return $this->presenceStatus;
    }

    /**
     * @param mixed $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * @return AbstractSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param mixed $trainee
     */
    public function setTrainee($trainee)
    {
        $this->trainee = $trainee;
    }

    /**
     * @return AbstractTrainee
     */
    public function getTrainee()
    {
        return $this->trainee;
    }

    /**
     * @return bool
     */
    public function isSendInscriptionStatusMail()
    {
        return $this->sendInscriptionStatusMail;
    }

    /**
     * @param bool $sendInscriptionStatusMail
     */
    public function setSendInscriptionStatusMail($sendInscriptionStatusMail)
    {
        $this->sendInscriptionStatusMail = $sendInscriptionStatusMail;
    }

    /**
     * @return \DateTime
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"api"})
     */
    public function getDate()
    {
        return $this->getCreatedAt();
    }

    /**
     * Set the default inscription status (1).
     *
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setDefaultInscriptionStatus(LifecycleEventArgs $eventArgs)
    {
        if( ! $this->getInscriptionStatus()) {
            $repository = $eventArgs->getEntityManager()->getRepository('SygeforInscriptionBundle:Term\InscriptionStatus');
            $status     = $repository->findOneBy(array('machineName' => 'waiting'));
            $this->setInscriptionStatus($status);
        }
    }

    /**
     * Save update date for property inscription status.
     *
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setInscriptionStatusUpdatedAtLifecycle(LifecycleEventArgs $eventArgs)
    {
        $uow       = $eventArgs->getEntityManager()->getUnitOfWork();
        $changeset = $uow->getEntityChangeSet($this);
        if (isset($changeset['inscriptionStatus'])) {
            $this->setInscriptionStatusUpdatedAt((new \DateTime('now', new \DateTimeZone('Europe/Paris'))));
        }
    }

    /**
     * @return \DateTime
     */
    public function getInscriptionStatusUpdatedAt()
    {
        return $this->inscriptionStatusUpdatedAt;
    }

    /**
     * @param \DateTime $inscriptionStatusUpdatedAt
     */
    public function setInscriptionStatusUpdatedAt($inscriptionStatusUpdatedAt)
    {
        $this->inscriptionStatusUpdatedAt = $inscriptionStatusUpdatedAt;
    }

    /**
     * @return \Sygefor\Bundle\CoreBundle\Entity\Organization
     */
    public function getOrganization()
    {
        return $this->getSession()->getTraining()->getOrganization();
    }

    /**
     * For activity report.
     *
     * @return string
     */
    public function getZoneCompetence()
    {
        $organization = $this->getSession()->getTraining()->getOrganization();

        // Etablissement de rattachement
        if($organization->getInstitution() && $this->getInstitution() === $organization->getInstitution()) {
            return 'Etablissement de rattachement';
        }

        // Agglomération
        if($this->getInstitution()) {
            $organizationCity = trim(current(preg_split('/cedex/si', $organization->getCity())));
            $institutionCity  = trim(current(preg_split('/cedex/si', $this->getInstitution()->getCity())));
            if($organizationCity === $institutionCity) {
                return 'Agglomération';
            }
        }

        // Zone de compétence
        $zip = null;
        if($this->getInstitution()) {
            $zip = $this->getInstitution()->getZip();
        }
        if( ! $zip) {
            $zip = $this->getZip();
        }
        $dpt = substr($zip, 0, 2);
        if(in_array($dpt, $organization->getDepartments(), true)) {
            return 'Zone de compétence';
        }

        return 'Hors zone';
    }

    /**
     * @return mixed
     */
    static public function getFormType()
    {
        return BaseInscriptionType::class;
    }

    /**
     * @return string
     */
    static public function getType()
    {
        return 'inscription';
    }
}
