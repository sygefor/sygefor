<?php

namespace Sygefor\Bundle\ApiBundle\Controller\Account;


use Doctrine\ORM\QueryBuilder;
use Sygefor\Bundle\InscriptionBundle\Entity\AbstractInscription;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\PresenceStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * This controller regroup actions related to attendance.
 *
 * @Route("/api/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class AttendanceAccountController extends Controller
{
    /**
     * All attendances of the trainee.
     *
     * @Route("/attendances", name="api.account.attendances", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.training", "api.attendance"})
     * @Method("GET")
     */
    public function attendancesAction(Request $request)
    {
        $qb          = $this->createQueryBuilder();
        $attendances = $qb->getQuery()->getResult();

        return $attendances;
    }

    /**
     * Single attendance.
     *
     * @Route("/attendance/{session}", name="api.account.attendance", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.training", "api.attendance"})
     * @Method("GET")
     *
     * @return AbstractInscription
     */
    public function attendanceAction($session, Request $request)
    {
        return $this->getAttendance($session);
    }

    /**
     * Download a material.
     *
     * @Route("/attendance/{session}/download/{material}", name="api.account.attendance.download", defaults={"_format" = "json"})
     * @Rest\View()
     * @Method("GET")
     */
    public function downloadAction(Request $request, $session, $material)
    {
        $attendance   = $this->getAttendance($session);
        $allMaterials = array();
        $material     = intval($material);

        // get all materials
        foreach ($attendance->getSession()->getMaterials() as $sessionMaterial) {
            $allMaterials[$sessionMaterial->getId()] = $sessionMaterial;
        }
        foreach ($attendance->getSession()->getTraining()->getMaterials() as $trainingMaterial) {
            $allMaterials[$trainingMaterial->getId()] = $trainingMaterial;
        }

        foreach ($allMaterials as $_material) {
            if ($_material->getId() === $material) {
                $material = $_material;
                if ($material->getType() === 'file') {
                    return $material->send();
                }
                else if ($material->getType() === 'link') {
                    return new RedirectResponse($_material->getUrl());
                }
            }
        }

        throw new NotFoundHttpException('Unknown resource.');
    }

    /**
     * Attestation of attendance.
     *
     * @Route("/attendance/{session}/attestation", name="api.account.attendance.attestation")
     * @Method("GET")
     */
    public function attestationAction($session, Request $request)
    {
        $attendance = $this->getAttendance($session);

        //filesystem for checking signature file existence

        // getting signature asset
        $signature = null;

        //checking signature file existence
        $fs = new Filesystem();
        if($fs->exists($this->get('kernel')->getRootDir() . '/../web/img/organization/' . $attendance->getSession()->getTraining()->getOrganization()->getCode() . '/signature.png' )) {
            $signature = '/img/organization/' . $attendance->getSession()->getTraining()->getOrganization()->getCode() . '/signature.png';
        }

        $pdf = $this->renderView('SygeforInscriptionBundle:Inscription:attestation.pdf.twig', array(
            'inscription' => $attendance,
            'signature'   => $signature,
        ));

        return new Response(
          $this->get('knp_snappy.pdf')->getOutputFromHtml($pdf, array('print-media-type' => null)), 200,
          array(
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="attestation.pdf"', )
        );
    }

    /**
     * Return the attendance belong to the session.
     *
     * @return AbstractInscription
     */
    private function getAttendance($session)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere('i.session = :session')
          ->setParameter('session', $session);
        $attendance = $qb->getQuery()->getOneOrNullResult();
        if( ! $attendance) {
            throw new NotFoundHttpException('Unknown attendance.');
        }

        return $attendance;
    }

    /**
     * Create a specific query builder for attendees.
     *
     * @return QueryBuilder
     */
    private function createQueryBuilder()
    {
        $em         = $this->getDoctrine()->getManager();
        $trainee    = $this->getUser();
        $repository = $em->getRepository('SygeforInscriptionBundle:AbstractInscription');
        /** @var QueryBuilder $qb */
        $qb = $repository->createQueryBuilder('i');
        // only for the current user
        $qb->where('i.trainee = :trainee')
            ->setParameter('trainee', $trainee);
        // only with the PRESENT status
        $qb->join('i.presenceStatus', 'p');
        $qb->andWhere('p.status = :presenceStatus')
            ->setParameter('presenceStatus', PresenceStatus::STATUS_PRESENT);
        // only past sessions
        $qb->join('i.session', 's');
        $qb->andWhere('s.dateBegin <= CURRENT_DATE()');

        return $qb;
    }
}
