<?php
namespace Sygefor\Bundle\TraineeBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\CoordinatesTrait;
use Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\UserBundle\AccessRight\SerializedAccessRights;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use JMS\Serializer\Annotation as Serializer;

/**
 * Trainee
 *
 * @ORM\Table(name="inscription", uniqueConstraints={@UniqueConstraint(name="traineesession_idx", columns={"trainee_id", "session_id"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"trainee", "session"}, message="Cet utilisateur est déjà inscrit à cette session !")
 */
class Inscription implements SerializedAccessRights
{
    use ORMBehaviors\Timestampable\Timestampable;
    use CoordinatesTrait;
    use ProfessionalSituationTrait;

    /**
     * @var integer id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Trainee", inversedBy="inscriptions")
     * @ORM\JoinColumn(name="trainee_id", referencedColumnName="id")
     * @Assert\NotNull(message="Vous devez sélectionner un stagiaire.")
     * @Serializer\Groups({"inscription", "session"})
     */
    protected $trainee;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Session", inversedBy="inscriptions")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id")
     * @Assert\NotNull()
     * @Serializer\Groups({"inscription", "trainee", "api"})
     */
    protected $session;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus")
     * @Assert\NotNull(message="Vous devez spécifier un status d'inscription.")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $inscriptionStatus;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $presenceStatus;

    /**
     * @ORM\OneToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\Evaluation", mappedBy="inscription", cascade={"persist", "merge", "remove"})
     * @Serializer\Groups({"api.attendance"})
     */
    protected $evaluation;

    /**
     * @var boolean
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
     * @return Session
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
     * @return Trainee
     */
    public function getTrainee()
    {
        return $this->trainee;
    }

    /**
     * @return mixed
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    /**
     * @param mixed $evaluation
     */
    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * @return boolean
     */
    public function isSendInscriptionStatusMail()
    {
        return $this->sendInscriptionStatusMail;
    }

    /**
     * @param boolean $sendInscriptionStatusMail
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
     * Set the default inscription status (1)
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setDefaultInscriptionStatus(LifecycleEventArgs $eventArgs)
    {
        if(!$this->getInscriptionStatus()) {
            $repository = $eventArgs->getEntityManager()->getRepository('SygeforTraineeBundle:Term\InscriptionStatus');
            $status = $repository->find(1);
            $this->setInscriptionStatus($status);
        }
    }

    /**
     * For activity report
     * @return string
     */
    public function getZoneCompetence()
    {
        $organization = $this->getSession()->getTraining()->getOrganization();

        // Etablissement de rattachement
        if($organization->getInstitution() && $this->getInstitution() == $organization->getInstitution()) {
            return "Etablissement de rattachement";
        }

        // Agglomération
        if($this->getInstitution()) {
            $organizationCity = trim(current(preg_split("/cedex/si", $organization->getCity())));
            $institutionCity = trim(current(preg_split("/cedex/si", $this->getInstitution()->getCity())));
            if($organizationCity == $institutionCity) {
                return "Agglomération";
            }
        }

        // Zone de compétence
        $zip = null;
        if($this->getInstitution()) {
            $zip = $this->getInstitution()->getPostal();
        }
        if(!$zip) {
            $zip = $this->getZip();
        }
        $dpt = substr($zip, 0, 2);
        if(in_array($dpt, $organization->getDepartments())) {
            return "Zone de compétence";
        }

        return "Hors zone";
    }

}
