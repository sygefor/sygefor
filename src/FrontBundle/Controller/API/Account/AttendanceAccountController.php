<?php

namespace FrontBundle\Controller\API\Account;

use AppBundle\Entity\Inscription;
use FrontBundle\Form\Type\EvaluationType;
use AppBundle\Entity\Evaluation\Evaluation;
use AppBundle\Entity\Term\Evaluation\Theme;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Evaluation\EvaluatedTheme;
use AppBundle\Entity\Evaluation\NotedCriterion;
use AppBundle\Entity\Term\Evaluation\Criterion;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sygefor\Bundle\ApiBundle\Controller\Account\AttendanceAccountController as BaseAttendanceAccountController;

/**
 * This controller regroup actions related to attendance.
 *
 * @Route("/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class AttendanceAccountController extends BaseAttendanceAccountController
{
    /**
     * All attendances of the trainee.
     *
     * @Route("/attendances", name="front.account.attendances")
     * @Template("@Front/Account/attendance/attendances.html.twig")
     * @Method("GET")
     */
    public function attendancesAction(Request $request)
    {
        return array('attendances' => parent::attendancesAction($request));
    }

    /**
     * Single attendance.
     *
     * @Route("/attendance/{session}", name="front.account.attendance")
     * @Template("@Front/Account/attendance/attendance.html.twig")
     * @Method("GET")
     */
    public function attendanceAction($session, Request $request)
    {
        return array('attendance' => parent::attendanceAction($session, $request));
    }

    /**
     * @Route("/attendance/{id}/evaluation", name="front.account.attendance.evaluation")
     * @ParamConverter("attendance", class="AppBundle:Inscription", options={"id" = "id"})
     * @Template("@Front/Account/attendance/evaluation.html.twig")
     */
    public function evaluationAction(Request $request, Inscription $attendance)
    {
        if ($attendance->getEvaluation()) {
            $this->get('session')->getFlashBag()->add('success', 'La session a déjà été évaluée.');

            return $this->redirectToRoute('front.account.attendances');
        }

        $evaluation = new Evaluation();
        $evaluation->setInscription($attendance);
        $attendance->setEvaluation($evaluation);
        $evaluationThemes = $this->getDoctrine()->getRepository(Theme::class)
            ->findBy(array(), array(Theme::orderBy() => 'ASC'));
        $evaluationCriterions = $this->getDoctrine()->getRepository(Criterion::class)
            ->findBy(array(), array('theme' => 'ASC', Criterion::orderBy() => 'ASC'));

        foreach ($evaluationThemes as $theme) {
            $evaluatedTheme = new EvaluatedTheme();
            $evaluatedTheme->setEvaluation($evaluation);
            $evaluatedTheme->setTheme($theme);
            $evaluation->addTheme($evaluatedTheme);
            foreach ($evaluationCriterions as $criterion) {
                if ($criterion->getTheme()->getId() === $evaluatedTheme->getTheme()->getId()) {
                    $evaluationNotedCriterion = new NotedCriterion();
                    $evaluationNotedCriterion->setTheme($evaluatedTheme);
                    $evaluationNotedCriterion->setCriterion($criterion);
                    $evaluatedTheme->addCriterion($evaluationNotedCriterion);
                }
            }
        }

        $form = $this->createForm(new EvaluationType(), $evaluation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($evaluation);
            $attendance->updateTimestamps();
            $this->getDoctrine()->getManager()->flush();
            $this->get('session')->getFlashBag()->add('success', "Les réponses ont bien été enregistrées. Merci d'avoir noté la session.");

            return $this->redirectToRoute('front.account.attendance', array('session' => $attendance->getSession()->getId()));
        }

        return array('attendance' => $attendance, 'form' => $form->createView());
    }

    /**
     * Download a material.
     *
     * @Route("/attendance/{session}/download/{material}", name="front.account.attendance.download")
     * @Method("GET")
     */
    public function downloadAction(Request $request, $session, $material)
    {
        return parent::downloadAction($request, $session, $material);
    }

    /**
     * Attestation of attendance.
     *
     * @Route("/attendance/{session}/attestation", name="front.account.attendance.attestation")
     * @Method("GET")
     */
    public function attestationAction($session, Request $request)
    {
        return parent::attestationAction($session, $request);
    }
}
