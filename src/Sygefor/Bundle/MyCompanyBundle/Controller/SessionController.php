<?php

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\MyCompanyBundle\Entity\Participation;
use Sygefor\Bundle\MyCompanyBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Controller\AbstractSessionController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/training/session")
 */
class SessionController extends AbstractSessionController
{
    protected $sessionClass = Session::class;
    protected $participationClass = Participation::class;
}
