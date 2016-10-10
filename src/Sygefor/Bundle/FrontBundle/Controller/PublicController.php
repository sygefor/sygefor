<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 11:00 AM
 */

namespace Sygefor\Bundle\FrontBundle\Controller;


use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\ApiBundle\Controller\TrainingController;
use Sygefor\Bundle\MyCompanybundle\Entity\Inscription;
use Sygefor\Bundle\FrontBundle\Form\InscriptionType;
use Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Symfony\Component\HttpFoundation\Request;
Use Elastica\Query;
Use Elastica\Filter\BoolAnd;
Use Elastica\Filter\Term;
Use Elastica\Filter\Range;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/")
 */
class PublicController extends Controller
{
    protected $apiTrainingController;

    public function __construct()
    {
        $this->apiTrainingController = new TrainingController();
    }

    /**
     * @Route("/{page}", name="front.public.index", requirements={"page": "\d+"})
     * @Template
     */
    public function indexAction(Request $request, $page = 1)
    {
        $this->apiTrainingController->setContainer($this->container);
        $search = $this->createProgramQuery($page);

        if ($request->get('shibboleth') == 1) {
            if ($request->get('error') == "activation") {
                $this->get('session')->getFlashBag()->add('warning', "Votre compte doit être activé par un administrateur avant de pouvoir vous connecter.");
            }
        }

        return array('user' => $this->getUser(), 'search' => $search, 'page' => $page);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining $training
     * @param null $sessionId
     * @param null $token
     *
     * @Route("/training/{id}/{sessionId}/{token}", name="front.public.training", requirements={"id": "\d+", "sessionId": "\d+"})
     * @ParamConverter("training", class="SygeforTrainingBundle:Training\AbstractTraining", options={"id" = "id"})
     * @Template("@SygeforFront/Public/program/training.html.twig")
     *
     * @return array
     */
    public function trainingAction(Request $request, AbstractTraining $training, $sessionId = null, $token = null)
    {
        $this->apiTrainingController->setContainer($this->container);
        $training = $this->apiTrainingController->trainingAction($training);
        $focusSession = null;
        foreach ($training->getSessions() as $session) {
            if ($session->getId() == $sessionId) {
                $focusSession = $session;
                break;
            }
        }

        $now = new \DateTime();
        $pastSessions = array();
        $upcomingSessions = array();
        /** @var AbstractSession $session */
        foreach ($training->getSessions() as $session) {

            $inscription = null;
            if ($this->getUser() instanceof AbstractTrainee) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $inscription = $em->getRepository('SygeforInscriptionBundle:AbstractInscription')->createQueryBuilder('inscription')
                ->leftJoin('SygeforTrainingBundle:Session\AbstractSession', 'session', 'WITH', 'inscription.session = session.id')
                ->leftJoin('SygeforTraineeBundle:AbstractTrainee', 'trainee', 'WITH', 'inscription.trainee = trainee.id')
                ->where('session.id = :sessionId')
                ->andWhere('trainee.id = :traineeId')
                ->setParameter('sessionId', $sessionId)
                ->setParameter('traineeId', $this->getUser()->getId())->getQuery()->execute();
            }

            $session->isRegistered = !empty($inscription);
            $session->getDateBegin() > $now ? $upcomingSessions[] = $session : $pastSessions[] = $session;
            if ($session->getRegistration() === $session::REGISTRATION_PRIVATE && (!method_exists($session, 'getModule') || !$session->getModule())) {
                $session->availablePrivateSession = true;
            }
            else {
                $session->availablePrivateSession = false;
            }
            if (method_exists($session, 'getModule') && $session->getModule()) {
                $session->moduleToken = md5($session->getTraining()->getType() . $session->getTraining()->getId()) === $token;
            }
        }

        if ($this->getUser() && !$this->getUser()->getIsActive()) {
            $this->get('session')->getFlashBag()->add('warning', "Vous ne pouvez pas vous inscrire à une session tant que votre compte n'a pas
             été validé par un administrateur.");
        }

        return array(
            'user' => $this->getUser(),
            'training' => $training,
            'session' => $focusSession,
            'upcomingSessions' => $upcomingSessions,
            'pastSessions' => $pastSessions,
            'token' => $token
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining $training
     * @param \Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession $session
     * @param null $token
     *
     * @Route("/training/inscription/{id}/{sessionId}/{token}", name="front.public.inscription", requirements={"id": "\d+", "sessionId": "\d+"})
     * @ParamConverter("training", class="SygeforTrainingBundle:Training\AbstractTraining", options={"id" = "id"})
     * @ParamConverter("session", class="SygeforTrainingBundle:Session\AbstractSession", options={"id" = "sessionId"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Template("@SygeforFront/Public/program/inscription.html.twig")
     *
     * @return array
     */
    public function inscriptionAction(Request $request, AbstractTraining $training, AbstractSession $session, $token = null)
    {
        // in case shibboleth authentication done but user has not registered his account
        if (!is_object($this->getUser())) {
            return $this->redirectToRoute('front.account.register');
        }

        if (!$this->getUser()->getIsActive()) {
            $this->get('session')->getFlashBag()->add('warning', "Vous ne pouvez pas vous inscrire à une session tant que votre compte n'a pas
             été validé par un administrateur.");
            return $this->redirectToRoute('front.public.training', array('id' => $training->getId(), 'sessionId' => $session->getId(), 'token' => $token));
        }

        $this->apiTrainingController->setContainer($this->container);
        $training = $this->apiTrainingController->trainingAction($training);
        if (method_exists($session, 'getModule') && $session->getModule()) {
            $session->moduleToken = md5($session->getTraining()->getType() . $session->getTraining()->getId()) === $token;
        }

        $inscription = $this->getDoctrine()->getManager()->getRepository('SygeforInscriptionBundle:AbstractInscription')->findOneBy(array(
            'trainee' => $this->getUser(),
            'session'=> $session
        ));
        if ($inscription) {
            throw new ForbiddenOverwriteException('An inscription has already been found');
        }
        if (!$inscription) {
            $inscription = new Inscription();
            $inscription->setTrainee($this->getUser());
            $inscription->setSession($session);
        }
        $inscription->setInscriptionStatus(
            $this->getDoctrine()->getRepository('SygeforInscriptionBundle:Term\InscriptionStatus')->findOneBy(
                array('machineName' => 'waiting')
            )
        );
        $form = $this->createForm(new InscriptionType(), $inscription);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($inscription);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Votre inscription a bien été enregistrée.');

                return $this->redirectToRoute(
                    'front.account.checkout', array(
                    'inscriptionId' => $inscription->getId())
                );
            }
        }

        return array(
            'user' => $this->getUser(),
            'form' => $form->createView(),
            'training' => $training,
            'session' => $session,
            'token' => $token
        );
    }

    /**
     * @Route("/login", name="front.public.login")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        return array('user' => $this->getUser());
    }

    /**
     * @Route("/contact", name="front.public.contact")
     * @Template()
     */
    public function contactAction(Request $request)
    {
        return array('user' => $this->getUser());
    }

    /**
     * @Route("/faq", name="front.public.faq")
     * @Template()
     */
    public function faqAction(Request $request)
    {
        return array('user' => $this->getUser());
    }

    /**
     * @Route("/about", name="front.public.about")
     * @Template()
     */
    public function aboutAction(Request $request)
    {
        return array('user' => $this->getUser());
    }

    /**
     * @Route("/legalNotice", name="front.public.legalNotice")
     * @Template()
     */
    public function legalNoticeAction(Request $request)
    {
        return array('user' => $this->getUser());
    }

    /**
     * @param $page
     * @param int $itemPerPage
     * @param $code
     * @return array
     */
    protected function createProgramQuery($page, $itemPerPage = 10, $code = null)
    {
        $search = $this->get('sygefor_training.session.search');
        if ($page) {
            $search->setPage($page);
            $search->setSize($itemPerPage);
        }

        // add filters
        $filters = new BoolAnd();
        if (!empty($code)) {
            $organization = new Term(array('training.organization.code' => $code));
            $filters->addFilter($organization);
        }

        $dateBegin = new Range('dateBegin', array("gte" => (new \DateTime("now", timezone_open('Europe/Paris')))->format('Y-m-d')));
        $filters->addFilter($dateBegin);

//        $types = new Terms('training.type', array('internship'));
//        $filters->addFilter($types);

        $search->addFilter('filters', $filters);
        $search->addSort('dateBegin');
        $search->addSort('training.name.source');

        return $search->search();
    }
}