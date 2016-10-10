<?php

namespace Sygefor\Bundle\FrontBundle\Controller;


use Doctrine\Common\Collections\ArrayCollection;
use Sygefor\Bundle\ApiBundle\Controller\Account\AttendanceAccountController as BaseAttendanceAccountController;
use Sygefor\Bundle\MyCompanyBundle\Entity\EvaluationNotedCriterion;
use Sygefor\Bundle\MyCompanybundle\Entity\Inscription;
use Sygefor\Bundle\FrontBundle\Form\EvaluationType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * This controller regroup actions related to attendance.
 *
 * @Route("/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class AttendanceAccountController extends BaseAttendanceAccountController
{
    /**
     * All attendances of the trainee
     * @Route("/attendances", name="front.account.attendances")
     * @Template("@SygeforFront/Account/attendance/attendances.html.twig")
     * @Method("GET")
     */
    public function attendancesAction(Request $request)
    {
        return array('user' => $this->getUser(), 'attendances' => parent::attendancesAction($request));
    }

    /**
     * Single attendance.
     *
     * @Route("/attendance/{session}", name="front.account.attendance")
     * @Template("@SygeforFront/Account/attendance/attendance.html.twig")
     * @Method("GET")
     */
    public function attendanceAction($session, Request $request)
    {
        /** @var Inscription $attendance */
        $attendance = parent::attendanceAction($session, $request);
        $session = $attendance->getSession();
        $allMaterials = new ArrayCollection();
        foreach ($session->getMaterials() as $material) {
            $allMaterials->add($material);
        }
        foreach ($session->getTraining()->getMaterials() as $material) {
            $allMaterials->add($material);
        }
        $attendance->getSession()->setAllMaterials($allMaterials);

        return array('user' => $this->getUser(), 'attendance' => $attendance);
    }

    /**
     * @Route("/attendance/{id}/evaluation", name="front.account.attendance.evaluation")
     * @ParamConverter("attendance", class="SygeforMyCompanyBundle:Inscription", options={"id" = "id"})
     * @Template("@SygeforFront/Account/attendance/evaluation.html.twig")
     */
    public function evaluationAction(Request $request, Inscription $attendance)
    {
        if ($attendance->getCriteria() && $attendance->getCriteria()->count() > 0) {
            throw new AccessDeniedHttpException("This session has already been evaluated.");
        }

        $evaluationCriterions = $this->getDoctrine()
            ->getRepository('SygeforMyCompanyBundle:Term\EvaluationCriterion')
            ->findAll();
        foreach ($evaluationCriterions as $evaluationCriterion) {
            $evaluationNotedCriterion = new EvaluationNotedCriterion();
            $evaluationNotedCriterion->setInscription($attendance);
            $evaluationNotedCriterion->setCriterion($evaluationCriterion);
            $attendance->addCriterion($evaluationNotedCriterion);
        }

        $form = $this->createForm(new EvaluationType(), $attendance);
        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                $this->get('session')->getFlashBag()->add('success', "Les réponses ont bien été enregistrées. Merci d'avoir noté la session.");
                return $this->redirectToRoute('front.account.attendance', array('session' => $attendance->getSession()->getId()));
            }
        }

        return array('user' => $this->getUser(), 'attendance' => $attendance, 'form' => $form->createView());
    }

    /**
     * Download a material
     * @Route("/attendance/{session}/download/{material}", name="front.account.attendance.download")
     * @Method("GET")
     */
    public function downloadAction(Request $request, $session, $material)
    {
        return parent::downloadAction($request, $session, $material);
    }

    /**
     * Attestation of attendance
     * @Route("/attendance/{session}/attestation", name="front.account.attendance.attestation")
     * @Method("GET")
     */
    public function attestationAction($session, Request $request)
    {
        return parent::attestationAction($session, $request);
    }
}
