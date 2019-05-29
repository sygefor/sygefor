<?php

namespace AppBundle\Entity\Trainee;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\CoordinatesTrait;
use AppBundle\Form\Type\Trainee\TraineeType;
use AppBundle\Entity\ProfessionalSituationTrait;
use Sygefor\Bundle\ApiBundle\Entity\AccountTrait;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use NotificationBundle\Mailer\MailerRecipientInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="trainee", uniqueConstraints={@ORM\UniqueConstraint(name="emailUnique", columns={"email"})}))
 * @ORM\Entity(repositoryClass="Sygefor\Bundle\ApiBundle\Repository\AccountRepository")
 * @UniqueEntity(fields={"email"}, message="Cette adresse email est déjà utilisée.")
 */
class Trainee extends AbstractTrainee implements \Serializable, UserInterface, AdvancedUserInterface, MailerRecipientInterface
{
    use AccountTrait;
    use CoordinatesTrait;
    use DisciplinaryTrait;
    use ProfessionalSituationTrait;

    public function __construct()
    {
        parent::__construct();

        $this->addressType = 0;
        $this->isActive = true;
        $this->sendCredentialsMail = true;
        $this->sendActivationMail = true;
	    $this->cgu = false;
	    $this->consent = false;
	    $this->newsletter = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return array('ROLE_TRAINEE');
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
        list($this->id) = unserialize($serialized);
    }

    /**
     * @return mixed
     */
    public static function getFormType()
    {
        return TraineeType::class;
    }

    /**
     * loadValidatorMetadata.
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        parent::loadValidatorMetadata($metadata);

        $metadata->addPropertyConstraint('email', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un email.',
        )));
        $metadata->addPropertyConstraint('phoneNumber', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un numéro de téléphone.',
            'groups' => 'api.profile',
        )));
    }
}
