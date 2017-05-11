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

    /**
     * @param Institution $institution
     * @param Correspondent $manager
     *
     * @Route("/{idInstitution}/manager/remove/{id}", requirements={"id" = "\d+"}, name="institution.removeManager", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("institution", class="SygeforMyCompanyBundle:Institution", options={"id" = "idInstitution"})
     * @ParamConverter("manager", class="SygeforInstitutionBundle:AbstractCorrespondent", options={"id" = "id"})
     * @SecureParam(name="institution", permissions="EDIT")
     * @Method("POST")
     *
     * @return mixed
     */
    public function removeManagerAction(Institution $institution, Correspondent $manager)
    {
        $em = $this->getDoctrine()->getManager();
        $institution->setManager(null);
        $em->remove($manager);
        $em->flush();
        $this->get('fos_elastica.index')->refresh();

        return $this->redirectToRoute('institution.view', array('id' => $institution->getId()));
    }
}
