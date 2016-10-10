<?php

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\MyCompanyBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Controller\AbstractTraineeController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/trainee")
 */
class TraineeController extends AbstractTraineeController
{
    protected $traineeClass = Trainee::class;
}
