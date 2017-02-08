<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/9/16
 * Time: 9:48 AM
 */

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Sygefor\Bundle\MyCompanyBundle\Form\CorrespondentType;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractCorrespondent;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 *
 * @ORM\Table(name="correspondent")
 * @ORM\Entity
 */
class Correspondent extends AbstractCorrespondent
{
    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=255, nullable=true)
     * @Serializer\Groups({"trainee", "institution", "api.institution"})
     * @Assert\Regex(pattern="/^(\+\d+(\s|-|.))?0\d(\s|-|.)?(\d{2}(\s|-|.)?){4}$/", message="Vous devez renseigner un numéro de téléphone")
     */
    protected $phoneNumber;

    /**
     * @var string
     * @Assert\NotBlank(message="Vous devez renseigner un email.")
     * @Assert\Email(message="Vous devez renseigner un email valide.")
     * @ORM\Column(name="email", type="string", length=128)
     * @Serializer\Groups({"trainee", "institution", "api.institution"})
     */
    protected $email;

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * loadValidatorMetadata.
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // PersonTrait
        $metadata->addPropertyConstraint('firstName', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un nom de famille.',
        )));
        $metadata->addPropertyConstraint('lastName', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un nom de famille.',
        )));
        $metadata->addPropertyConstraint('email', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un email.',
        )));
    }

    public static function getFormType()
    {
        return CorrespondentType::class;
    }
}