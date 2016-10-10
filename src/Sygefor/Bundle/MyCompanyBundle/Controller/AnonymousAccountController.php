<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 10:43 AM
 */

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\ApiBundle\Controller\Account\AbstractAnonymousAccountController;
use Sygefor\Bundle\MyCompanyBundle\Entity\Trainee;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * This controller regroup all public actions relative to account.
 *
 * @Route("/api/account")
 */
class AnonymousAccountController extends AbstractAnonymousAccountController
{
    protected $traineeClass = Trainee::class;
}