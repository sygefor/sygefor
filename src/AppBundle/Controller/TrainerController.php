<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Trainer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sygefor\Bundle\CoreBundle\Controller\AbstractTrainerController;

/**
 * Class TrainerController.
 *
 * @Route("/trainer")
 */
class TrainerController extends AbstractTrainerController
{
    protected $trainerClass = Trainer::class;
}
