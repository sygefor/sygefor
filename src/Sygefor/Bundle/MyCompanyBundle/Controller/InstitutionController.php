<?php

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\MyCompanyBundle\Entity\Correspondent;
use Sygefor\Bundle\MyCompanyBundle\Entity\Institution;
use Sygefor\Bundle\InstitutionBundle\Controller\AbstractInstitutionController;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\SatisfiesParentSecurityPolicy;

/**
 * @Route("/institution")
 */
class InstitutionController extends AbstractInstitutionController
{
    protected $institutionClass = Institution::class;
}
