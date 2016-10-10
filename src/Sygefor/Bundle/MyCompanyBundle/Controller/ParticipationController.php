<?php

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\MyCompanyBundle\Entity\Participation;
use Sygefor\Bundle\TrainingBundle\Controller\AbstractParticipationController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/participation")
 */
class ParticipationController extends AbstractParticipationController
{
    protected $participationClass = Participation::class;
}
