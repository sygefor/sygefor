<?php

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Symfony\Component\Security\Core\User\UserInterface;
use Sygefor\Bundle\MyCompanyBundle\Form\TraineeType;
use Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\TraineeBundle\Entity\DisciplinaryTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *
 * @ORM\Table(name="trainee")
 * @ORM\Entity
 * @UniqueEntity(fields={"email", "organization"}, message="Cette adresse email est déjà utilisée.", ignoreNull=true, groups={"Default", "trainee"})
 */
class Trainee extends AbstractTrainee implements UserInterface
{
    use DisciplinaryTrait;

    /**
     * @return mixed
     */
    static public function getFormType()
    {
        return TraineeType::class;
    }
}
