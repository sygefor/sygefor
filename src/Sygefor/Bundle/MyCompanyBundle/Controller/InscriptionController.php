<?php

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\MyCompanyBundle\Entity\Inscription;
use Sygefor\Bundle\InscriptionBundle\Controller\AbstractInscriptionController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/inscription")
 */
class InscriptionController extends AbstractInscriptionController
{
    protected $inscriptionClass = Inscription::class;
}
