<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 09/12/2015
 * Time: 16:26.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Session\Participation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sygefor\Bundle\CoreBundle\Controller\AbstractParticipationController;

/**
 * Class ParticipationController.
 *
 * @Route("/participation")
 */
class ParticipationController extends AbstractParticipationController
{
    protected $participationClass = Participation::class;
}
