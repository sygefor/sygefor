<?php

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\MyCompanyBundle\Entity\Trainer;
use Sygefor\Bundle\TrainerBundle\Controller\AbstractTrainerController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/trainer")
 */
class TrainerController extends AbstractTrainerController
{
    protected $trainerClass = Trainer::class;
}
