<?php

/**
 * Created by PhpStorm.
 * Organization: erwan
 * Date: 5/30/16
 * Time: 5:41 PM.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Controller\AbstractOrganizationController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class OrganizationController.
 *
 * @Route("/admin/organizations")
 */
class OrganizationController extends AbstractOrganizationController
{
    protected $organizationClass = Organization::class;
}
