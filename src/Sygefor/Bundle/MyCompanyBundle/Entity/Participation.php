<?php

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractParticipation;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="participation")
 * @ORM\Entity
 */
class Participation extends AbstractParticipation
{

}
