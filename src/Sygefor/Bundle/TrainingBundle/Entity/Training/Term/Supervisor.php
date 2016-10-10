<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 6/17/16
 * Time: 5:34 PM.
 */
namespace Sygefor\Bundle\TrainingBundle\Entity\Training\Term;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;
use Sygefor\Bundle\TrainingBundle\Form\SupervisorType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Responsable pédagogique.
 *
 * @ORM\Table(name="supervisor")
 * @ORM\Entity
 */
class Supervisor extends AbstractTerm implements VocabularyInterface
{
    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=50, nullable=true)
     * @Serializer\Groups({"Default", "api"})
     */
    protected $firstName;

    /**
     * @var string
     * @Assert\Email(message="Vous devez renseigner un email valide.")
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     * @Serializer\Groups({"Default", "api"})
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=255, nullable=true)
     * @Serializer\Groups({"Default", "api"})
     */
    protected $phoneNumber;

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
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
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "api"})
     */
    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getName();
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->getFullName();
    }

    /**
     * returns the form type name for template edition.
     *
     * @return string
     */
    public static function getFormType()
    {
        return SupervisorType::class;
    }

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return 'Responsable pédagogique';
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_LOCAL;
    }
}
