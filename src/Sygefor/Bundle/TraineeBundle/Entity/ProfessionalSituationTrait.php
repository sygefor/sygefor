<?php
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 */
namespace Sygefor\Bundle\TraineeBundle\Entity;

use Sygefor\Bundle\CoreBundle\Entity\DisciplinaryTrait;
use Sygefor\Bundle\CoreBundle\Entity\PublicCategoryTrait;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublicType;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Institution;
use Symfony\Component\Validator\ExecutionContextInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class ProfessionalSituationTrait
 * @package Sygefor\Bundle\TraineeBundle\Entity
 */
trait ProfessionalSituationTrait {
    use DisciplinaryTrait, PublicCategoryTrait;

    /**
     * @var string Institution
     *
     * @Assert\NotNull(message="Vous devez renseigner un établissement ou une entreprise.", groups={"api.profile"})
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\Institution")
     * @Serializer\Groups({"trainee", "inscription", "session", "api.profile"})
     */
    protected $institution;

    /**
     * @var string otherInstitution
     * Assert : @see additionalAsserts
     * @ORM\Column(name="other_institution", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainee", "inscription", "session", "api.profile"})
     */
    protected $otherInstitution;

    /**
     * @var string service
     * @ORM\Column(name="service", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainee", "inscription", "api.profile"})
     */
    protected $service;

    /**
     * @ORM\Column(name="is_paying", type="boolean")
     * @Serializer\Groups({"trainee", "inscription", "api.profile","api.token"})
     */
    protected $isPaying = false;

    /**
     * @var string status
     * @ORM\Column(name="function", type="string", length=512, nullable=true)
     * @Serializer\Groups({"trainee", "inscription", "api.profile"})
     */
    protected $status;

    /**
     * Assert : @see additionalAsserts
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TrainingBundle\Entity\Term\TeachingCursus")
     * @Serializer\Groups({"trainee", "inscription", "api.profile"})
     */
    protected $teachingCursus;

    /**
     * Copy professional situation informations from another entity
     *
     * @param ProfessionalSituationTrait $entity
     */
    public function copyProfessionalSituation($entity)
    {
        $this->setDisciplinary($entity->getDisciplinary());
        $this->setInstitution($entity->getInstitution());
        $this->setOtherInstitution($entity->getOtherInstitution());
        $this->setPublicType($entity->getPublicType());
        $this->setService($entity->getService());
        $this->setIsPaying($entity->getIsPaying());
        $this->setStatus($entity->getStatus());
        $this->setTeachingCursus($entity->getTeachingCursus());
    }

    /**
     * @param string $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }
    /**
     * @return Institution
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param string $otherInstitution
     */
    public function setOtherInstitution($otherInstitution)
    {
        $this->otherInstitution = $otherInstitution;
    }

    /**
     * @return string
     */
    public function getOtherInstitution()
    {
        return $this->otherInstitution;
    }

    /**
     * @param string $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return boolean
     */
    public function getIsPaying()
    {
        return $this->isPaying;
    }

    /**
     * @param boolean $isPaying
     */
    public function setIsPaying($isPaying)
    {
        $this->isPaying = $isPaying;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $teachingCursus
     */
    public function setTeachingCursus($teachingCursus)
    {
        $this->teachingCursus = $teachingCursus;
    }

    /**
     * @return mixed
     */
    public function getTeachingCursus()
    {
        return $this->teachingCursus;
    }

    /**
     * Additional asserts
     *
     * @Assert\Callback(groups={"api.profile"})
     */
    public function additionalAsserts(ExecutionContextInterface $context)
    {
        $publicCategory = $this->getPublicCategory();
        $professionalSituation = $this->getProfessionalSituation();

        if(!$publicCategory) {
            $context->addViolationAt('publicCategory', "Vous devez renseigner une catégorie de public.");
        } elseif(!$professionalSituation && $publicCategory->hasChildren()) {
            $context->addViolationAt('professionalSituation', "Vous devez préciser une situation professionnelle.");
        }

        $isTeacher = $this->belongToPublicType(1);   // Enseignant
        $isStudent = $this->belongToPublicType(20);  // Etudiant
        $isDoctor = $this->belongToPublicType(8);    // Doctorant

        if(!$this->getDisciplinaryDomain() && ($isTeacher || $isDoctor)) {
            $context->addViolationAt('disciplinaryDomain', "Vous devez renseigner un domaine disciplinaire.");
        } else if(!$this->getDisciplinary() && ($isTeacher || $isStudent || $isDoctor) && $this->getDisciplinaryDomain()->hasChildren()) {
            $context->addViolationAt('disciplinary', "Vous devez préciser une discipline.");
        }

        if(!$this->getTeachingCursus() && ($isStudent || $isDoctor)) {
            $context->addViolationAt('teachingCursus', "Vous devez renseigner un cursus d'enseignement.");
        }
    }
}
