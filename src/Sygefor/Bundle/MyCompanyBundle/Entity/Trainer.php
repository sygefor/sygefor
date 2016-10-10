<?php

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Sygefor\Bundle\TrainerBundle\Entity\AbstractTrainer;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as Serializer;

/**
 *
 * @ORM\Table(name="trainer")
 * @ORM\Entity
 * @UniqueEntity(fields={"email", "organization"}, message="Cette adresse email est déjà utilisée.", ignoreNull=true, groups={"Default", "trainer"})
 */
class Trainer extends AbstractTrainer
{

}
