<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 10:42 AM
 */

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\ApiBundle\Controller\Account\AbstractRegistrationAccountController;
use Sygefor\Bundle\MyCompanyBundle\Entity\Inscription;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * This controller regroup actions related to registration.
 *
 * @Route("/api/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class RegistrationAccountController extends AbstractRegistrationAccountController
{
    protected $inscriptionClass = Inscription::class;

    protected $sendCheckoutNotificationTemplates = 'SygeforMyCompanyBundle:Account/Registration:authorization.pdf.twig';

    protected $authorizationTemplate = 'SygeforMyCompanyBundle:Account/Registration:authorization.pdf.twig';
}