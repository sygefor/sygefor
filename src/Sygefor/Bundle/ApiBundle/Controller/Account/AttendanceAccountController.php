<?php
namespace Sygefor\Bundle\ApiBundle\Controller\Account;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Elastica\Filter\Query;
use Elastica\Query\FuzzyLikeThis;
use Elastica\Query\Match;
use Elastica\Query\MoreLikeThis;
use Elastica\Query\QueryString;
use Elastica\Suggest\Phrase;
use FOS\RestBundle\View\View;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Sygefor\Bundle\ApiBundle\Controller\SecurityController;
use Sygefor\Bundle\ApiBundle\Form\Type\ProfileType;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyRegistry;
use Sygefor\Bundle\TraineeBundle\Entity\Evaluation;
use Sygefor\Bundle\TraineeBundle\Entity\EvaluationNotedCriterion;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeArray;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeRepository;
use Sygefor\Bundle\TraineeBundle\Form\ApiRegisterType;
use Sygefor\Bundle\TraineeBundle\Form\ArrayTraineeType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeArrayType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeType;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

/**
 * This controller regroup actions related to attendance
 *
 * @package Sygefor\Bundle\TraineeBundle\Controller
 * @Route("/api/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class AttendanceAccountController extends Controller
{
    /**
     * All attendances of the trainee
     *
     * @Route("/attendances", name="api.account.attendances", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.training", "api.attendance"})
     * @Method("GET")
     */
    public function attendancesAction(Request $request)
    {
        $qb = $this->createQueryBuilder();
        $attendances = $qb->getQuery()->getResult();
        return $attendances;
    }

    /**
     * Single attendance
     *
     * @Route("/attendance/{session}", name="api.account.attendance", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.training", "api.attendance"})
     * @Method("GET")
     * @return Inscription
     */
    public function attendanceAction($session, Request $request)
    {
        return $this->getAttendance($session);
    }

    /**
     * Download a material
     *
     * @Route("/attendance/{session}/download/{material}", name="api.account.attendance.download", defaults={"_format" = "json"})
     * @Rest\View()
     * @Method("GET")
     */
    public function downloadAction($session, $material, Request $request)
    {
        /** @var EntityManager $em */
        //$material = $this->getDoctrine()->getManager()->getRepository('SygeforTrainingBundle:Material')->find($material);
        $attendance = $this->getAttendance($session);
        foreach($attendance->getSession()->getTraining()->getMaterials() as $_material) {
            if($_material->getId() == $material) {
                $material = $_material;
                if ($material->getType() == "file"){
                    return $material->send();
                }
                else if ($material->getType() == "link") {
                    return new RedirectResponse($_material->getUrl());
                }
            }
        }
        throw new NotFoundHttpException('Unknown resource.');
    }

    /**
     * Evaluate
     *
     * @Route("/attendance/{session}/evaluate", name="api.account.attendance.evaluate", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.training", "api.attendance"})
     * @Method("POST")
     */
    public function evaluateAction($session, Request $request)
    {
        $attendance = $this->getAttendance($session);
        if($attendance->getEvaluation()) {
            throw new AccessDeniedHttpException("This session has already been evaluated.");
        }
        $evaluation = new Evaluation($attendance);

        // message
        $message = $request->request->get('message');
        $evaluation->setMessage($message);

        // notes
        $notes = $request->request->get('notes');
        $repository = $this->getDoctrine()->getManager()->getRepository('SygeforTraineeBundle:Term\\EvaluationCriterion');
        $criteria = $repository->findAll();
        foreach($criteria as $criterion) {
            $note = @(int)$notes[$criterion->getId()];
            if(empty($note)) {
                throw new BadRequestHttpException('Missing criterion '.$criterion->getId().' (' . $criterion->getName() . ')');
            }
            $notedCriterion = new EvaluationNotedCriterion();
            $notedCriterion->setCriterion($criterion);
            $notedCriterion->setNote($note);
            $evaluation->addCriterion($notedCriterion);
        }

        // persist to em
        $em = $this->getDoctrine()->getManager();
        $em->persist($evaluation);
        $em->flush();

        return $evaluation;
    }

    /**
     * Attestation of attendance
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
        if($fs->exists($this->get('kernel')->getRootDir() . '/../web/img/urfist/'.$attendance->getSession()->getTraining()->getOrganization()->getCode().'/signature.png' )) {
            $signature = '/img/urfist/'.$attendance->getSession()->getTraining()->getOrganization()->getCode().'/signature.png';
        }

        $pdf = $this->renderView("SygeforTraineeBundle:Inscription:attestation.pdf.twig", array(
            'inscription' => $attendance,
            'signature' => $signature
        ));

        return new Response(
          $this->get('knp_snappy.pdf')->getOutputFromHtml($pdf, array("print-media-type" => null)), 200,
          array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="attestation.pdf"')
        );
    }

    /**
     * Return the attendance belong to the session
     * @return Inscription
     */
    private function getAttendance($session)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere('i.session = :session')
          ->setParameter('session', $session);
        $attendance = $qb->getQuery()->getOneOrNullResult();
        if(!$attendance) {
            throw new NotFoundHttpException('Unknown attendance.');
        }
        return $attendance;
    }

    /**
     * Create a specific query builder for attendees
     * @return QueryBuilder
     */
    private function createQueryBuilder()
    {
        $em = $this->getDoctrine()->getManager();
        $trainee = $this->getUser();
        $repository = $em->getRepository('SygeforTraineeBundle:Inscription');
        /** @var QueryBuilder $qb */
        $qb = $repository->createQueryBuilder('i');
        // only for the current user
        $qb->where('i.trainee = :trainee')
            ->setParameter('trainee', $trainee);
        // only with the PRESENT status
        $qb->andWhere('i.presenceStatus = :presenceStatus')
            ->setParameter('presenceStatus', PresenceStatus::STATUS_PRESENT);
        // only past sessions
        $qb->join('i.session', 's');
        $qb->andWhere('s.dateBegin <= CURRENT_DATE()');
        return $qb;
    }
}
