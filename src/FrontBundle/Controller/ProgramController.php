<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 11:00 AM.
 */

namespace FrontBundle\Controller;

use Elastica\Facet\Terms;
use Elastica\Filter\BoolNot;
use Elastica\Filter\Term;
use Elastica\Filter\Range;
use Elastica\Filter\BoolAnd;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use AppBundle\Entity\Inscription;
use AppBundle\Entity\Organization;
use FrontBundle\Utils\FormPagination;
use AppBundle\Entity\Term\Session\Place;
use AppBundle\Entity\Term\Training\Theme;
use FrontBundle\Form\Type\InscriptionType;
use FrontBundle\Form\Type\ProgramFilterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee;
use Sygefor\Bundle\CoreBundle\Entity\AbstractSession;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;
use Sygefor\Bundle\CoreBundle\Utils\Search\SearchService;
use Sygefor\Bundle\CoreBundle\Entity\AbstractInscription;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sygefor\Bundle\CoreBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublipostTemplate;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;

/**
 * @Route("/")
 */
class ProgramController extends Controller
{
    protected $organizationFacets;

    public function __construct()
    {
        $this->organizationFacets = [
	        'organization' => 'training.organization.name.source',
            'theme' => 'training.theme.id',
            'place' => 'place.source',
            'year' => 'year',
            'semester' => 'semester',
            'typeLabel' => 'training.typeLabel.source',
        ];
    }

	/**
	 * @Route("/program/{page}", name="front.program.index", requirements={"page": "\d+"}, defaults={"id": null})
	 * @Route("/centre/{code}/{page}", name="front.program.organization", requirements={"page": "\d+"})
	 * @ParamConverter("organization", class="AppBundle:Organization", options={"code" = "code"}, isOptional=true)
	 *
	 * @param Request      $request
	 * @param Organization $organization
	 * @param int          $page
	 *
	 * @return Response
	 */
	public function indexAction(Request $request, Organization $organization = null, $page = 1)
	{
		$this->noneActiveUser(null, null, null, false, $request);

		// get pagination form options
		$paginationParams = $request->query->all();
		$paginationParams = $this->getFormPaginationValue($paginationParams);

		if ($organization) {
			unset($this->organizationFacets['organization']);
		}

		// init search request
		$search = $this->get('sygefor_training.session.search');
		$filters = $this->getSessionFilters($search, $page, ($organization ? $organization->getCode() : null));
		$search->filterQuery($filters);
		$this->addFacets($search);
		$result = $search->search();

		// handle form options
		$form = $this->createForm(ProgramFilterType::class, null, array_merge($paginationParams, array(
				'facets' => $result['facets'],
				'entities' => [
					'organization' => $this->getDoctrine()->getRepository(Organization::class)->findAll(),
					'place' => $this->getDoctrine()->getRepository(Place::class)->findAll(),
					'theme' => $this->getDoctrine()->getRepository(Theme::class)->findAll(),
				], )
		));

		// if get form options have been overriden by post form options
		$beforeHandleOptions = FormPagination::getFormValues($form);
		$this->formFilter($search, $form, $request, $paginationParams);
		$afterHandleOptions = FormPagination::getFormValues($form);
		// force first page
		if ($beforeHandleOptions !== $afterHandleOptions) {
			$page = 1;
			$search->setPage(1);
		}
		// search
		$search = $search->search();

		return $this->render('@Front/Program/program.html.twig', array(
			'form' => $form->createView(),
			'search' => $search,
			'organization' => $organization,
			'page' => $page,
			'user' => $this->getUser(),
			'paginationFormFilters' => FormPagination::getPaginationFieldValues($form),
		));
	}

	/**
	 * @Route("/{page}", name="front.program.promoted", requirements={"page": "\d+"})
	 *
	 * @param Request $request
	 * @param int     $page
	 *
	 * @return Response
	 */
	public function promotedAction(Request $request, $page = 1)
	{
		$this->noneActiveUser(null, null, null, false, $request);

		// init search request
		$search = $this->get('sygefor_training.session.search');
		$filters = $this->getSessionFilters($search, $page, null, 10);
		$notCancelled = new BoolNot(new Term(array('status' => AbstractSession::STATUS_CANCELED)));
		$filters->addFilter($notCancelled);

		// get promoted sessions
		$promotedFilters = clone $filters;
		$promote = new Term(array('promote' => true));
		$promotedFilters->addFilter($promote);
		$search->addFilter('filters', $promotedFilters);
		$promotedResults = $search->search();

		// get non promoted sessions
		$notPromotedFilters = clone $filters;
		$promote = new Term(array('promote' => false));
		$notPromotedFilters->addFilter($promote);
		$search->addFilter('filters', $notPromotedFilters);
		$notPromotedResults = $search->search();

		// return 10 results with promoted sessions first and none promoted ones to complete if necessary
		$totalItemNumber = count($promotedResults['items']) + count($notPromotedResults['items']);
		if ($totalItemNumber > 10) {
			for ($i = 10 - count($promotedResults['items']); $i < 10; $i++) {
				array_pop($notPromotedResults['items']);
			}
		}
		$allItems = array_merge($promotedResults['items'], $notPromotedResults['items']);
		usort($allItems, function($a, $b) {
			return $a['dateBegin'] > $b['dateBegin'];
		});

		return $this->render('@Front/Program/index.html.twig', array(
			'search' => [
				'total' => count($promotedResults['items']) + count($notPromotedResults['items']),
				'pageSize' => 10,
				'items' => $allItems,
			],
			'page' => $page,
		));
	}

    /**
     * @param Request          $request
     * @param AbstractTraining $training
     * @param null             $sessionId
     * @param null             $token
     *
     * @Route("/training/{id}/{sessionId}/{token}", name="front.program.training", requirements={"id": "\d+", "sessionId": "\d+"})
     * @ParamConverter("training", class="SygeforCoreBundle:AbstractTraining", options={"id" = "id"})
     *
     * @return array
     */
    public function trainingAction(Request $request, AbstractTraining $training, $sessionId = null, $token = null)
    {
        $training = $this->get('sygefor_api.training')->trainingAction($training);
        $focusSession = null;
        foreach ($training->getSessions() as $session) {
            if ($session->getId() == $sessionId) {
                $focusSession = $session;
                break;
            }
        }

        $inscription = null;
        $now = new \DateTime();
        $pastSessions = array();
        $upcomingSessions = array();
        /** @var AbstractSession $session */
        foreach ($training->getSessions() as $session) {
            if ($this->getUser() instanceof AbstractTrainee) {
                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $inscription = $em->getRepository(AbstractInscription::class)->createQueryBuilder('inscription')
                    ->leftJoin(AbstractSession::class, 'session', 'WITH', 'inscription.session = session.id')
                    ->leftJoin(AbstractTrainee::class, 'trainee', 'WITH', 'inscription.trainee = trainee.id')
                    ->where('session.id = :sessionId')
                    ->andWhere('trainee.id = :traineeId')
                    ->setParameter('sessionId', $sessionId)
                    ->setParameter('traineeId', $this->getUser()->getId())->getQuery()->execute();
            }

            $session->isRegistered = !empty($inscription);
            $inscription = !empty($inscription) ? current($inscription) : null;
            if ($session->isRegistered || $session->isDisplayOnline()) {
                $session->getDateBegin() > $now ? $upcomingSessions[] = $session : $pastSessions[] = $session;
                if ($token && $token === md5($session->getId() + $session->getTraining()->getId()) && $session->getRegistration() === $session::REGISTRATION_PRIVATE) {
                    $session->availablePrivateSession = true;
                } else {
                    $session->availablePrivateSession = false;
                }
            }
        }

        if ($this->getUser() && !$this->getUser()->getIsActive()) {
            $this->get('session')->getFlashBag()->add('warning', "Vous ne pouvez pas vous inscrire à une session tant que votre compte n'a pas
             été validé par un administrateur.");
        }

        return $this->render('@Front/Program/training/training.html.twig',  array(
            'training' => $training,
            'session' => $focusSession,
            'upcomingSessions' => $upcomingSessions,
            'pastSessions' => $pastSessions,
            'displayPrivateDocuments' => $inscription instanceof Inscription && md5($inscription->getId()) === $token,
            'token' => $token,
        ));
    }

    /**
     * @param $id
     *
     * @Route("/training/pdf/{id}", name="front.training.pdf", requirements={"id": "\d+"})
     *
     * @return array
     */
    public function trainingPdfAction($id)
    {
        $template = $this->getDoctrine()->getRepository(PublipostTemplate::class)->findOneBy([
            'machineName' => 'export_pdf'
        ]);
        if ($template) {
            $file = $this->get('sygefor_core.batch.publipost.session')->execute([$id], ['template' => $template->getId()]);

            return $this->get('sygefor_core.batch.publipost.session')->sendFile($file['fileUrl'], 'Programme.pdf', ['pdf' => true]);
        }

        throw new NotFoundHttpException('No programme template has been found');
    }

    /**
     * @param AbstractSession $session
     *
     * @Route("/training/session/{id}/contact", name="front.program.session.place", requirements={"id": "\d+"})
     * @ParamConverter("session", class="SygeforCoreBundle:AbstractSession", options={"id" = "id"})
     *
     * @return array
     */
    public function sessionPlaceAction(AbstractSession $session)
    {
        return $this->render('@Front/Program/training/session/place.html.twig',  array(
            'place' => $session->getPlace(),
        ));
    }

    /**
     * @param Request          $request
     * @param AbstractTraining $training
     * @param AbstractSession  $session
     * @param null             $token
     *
     * @Route("/training/inscription/{id}/{sessionId}/{token}", name="front.program.session.inscription", requirements={"id": "\d+", "sessionId": "\d+"})
     * @ParamConverter("training", class="SygeforCoreBundle:AbstractTraining", options={"id" = "id"})
     * @ParamConverter("session", class="SygeforCoreBundle:AbstractSession", options={"id" = "sessionId"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return array
     */
    public function sessionInscriptionAction(Request $request, AbstractTraining $training, AbstractSession $session, $token = null)
    {
	    $ret = $this->noneActiveUser($training, $session, $token, true, $request);
	    if ($ret !== false) {
		    return $ret;
	    }

        $training = $this->get('sygefor_api.training')->trainingAction($training);
        $latestInscriptions = Inscription::getTraineeThemeInscription(
            $this->get('sygefor_inscription.search'),
            $this->getUser(),
            $training->getTheme()
        );

        $inscription = $this->getDoctrine()->getManager()->getRepository(AbstractInscription::class)->findOneBy(array(
            'trainee' => $this->getUser(),
            'session' => $session,
        ));
        if ($inscription) {
            throw new ForbiddenOverwriteException('An inscription has already been found');
        }
        if (!$inscription) {
            if ($session->getRegistration() === AbstractSession::REGISTRATION_PRIVATE) {
                if (!($token && $token === md5($session->getId() + $session->getTraining()->getId()) && $session->getRegistration())) {
                    throw new AccessDeniedException('The session '.$session->getId().' is private');
                }
            }
            $inscription = new Inscription();
            $inscription->setTrainee($this->getUser());
            $inscription->setSession($session);
        }
        $inscription->setInscriptionStatus(
            $this->getDoctrine()->getRepository(InscriptionStatus::class)->findOneBy(
                array('machineName' => 'waiting')
            )
        );
        $form = $this->createForm(new InscriptionType(), $inscription);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($inscription);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Votre inscription a bien été enregistrée.');

            return $this->redirectToRoute(
                'front.account.checkout', array(
                    'inscriptionId' => $inscription->getId(), )
            );
        }

        return $this->render('@Front/Program/training/session/inscription.html.twig', array(
            'latestInscriptions' => $latestInscriptions,
            'form' => $form->createView(),
            'training' => $training,
            'session' => $session,
            'token' => $token,
        ));
    }

	protected function noneActiveUser($training, $session, $token, $forceRegister, $request = null)
	{
		if ($request->get('shibboleth') == 1) {
			if ($request->get('error') == 'activation') {
				$this->get('session')->getFlashBag()->add('warning', 'Votre compte n\'est pas activé.');
			}
		}

		// in case shibboleth authentication done but user has not registered his account
		if (!is_object($this->getUser()) && $forceRegister) {
			return $this->redirectToRoute('front.account.register');
		}

		if ($this->getUser() && !$this->getUser()->getIsActive()) {
			$this->get('session')->getFlashBag()->add('warning', "Vous ne pouvez pas vous inscrire à une session tant
                que votre compte n'a pas été validé par un administrateur.");

			if ($training && $session) {
				return $this->redirectToRoute('front.program.training', array('id' => $training->getId(), 'sessionId' => $session->getId(), 'token' => $token));
			}
		}

		return false;
	}

    /**
     * @param SearchService $search
     * @param $page
     * @param null $code
     * @param int  $itemPerPage
     *
     * @return BoolAnd
     */
    protected function getSessionFilters(SearchService $search, $page, $code = null, $itemPerPage = 25)
    {
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

        $dateBegin = new Range('dateBegin', array('gte' => (new \DateTime('now', timezone_open('Europe/Paris')))->format('Y-m-d')));
        $filters->addFilter($dateBegin);

        $displayOnline = new Term(array('displayOnline' => true));
        $filters->addFilter($displayOnline);

        $firstLongTrainingSession = new BoolNot((new Term(array('firstLongTrainingSession' => false))));
        $filters->addFilter($firstLongTrainingSession);

        $search->addSort('limitRegistrationDate');
        $search->addSort('dateBegin');
        $search->addSort('training.name.source');

        return $filters;
    }

    /**
     * @param SearchService $search
     */
    protected function addFacets(SearchService $search)
    {
        foreach ($this->organizationFacets as $name => $field) {
            $facet = new Terms($name);
            $facet->setField($field);
	        $facet->setSize(999);
            $search->getQuery()->addFacet($facet);
        }
    }

	/**
	 * @param SearchService $search
	 * @param Form          $form
	 * @param Request       $request
	 * @param array         $paginationParams
	 */
	protected function formFilter(SearchService $search, Form $form, Request $request, $paginationParams)
	{
		$boolAnd = new BoolAnd();
		$form = $form->handleRequest($request);
		$fields = $paginationParams;
		if ($form->isSubmitted() && $form->isValid() && $form->getData()) {
			$fields = array_merge($paginationParams, $form->getData());
		}
		foreach ($fields as $field => $data) {
			if (!empty($data)) {
				if (is_string($data)) {
					$data = json_decode($data, true);
				}
				$term = new \Elastica\Filter\Terms($this->organizationFacets[$field], $data);
				$boolAnd->addFilter($term);
			}
		}

		if (count($boolAnd->getFilters()) > 0) {
			$search->addFilter('filters', $boolAnd);
		}
	}

	protected function getFormPaginationValue($paginationParams)
	{
		foreach (['page', 'shibboleth', 'error'] as $field) {
			if (isset($paginationParams[$field])) {
				unset($paginationParams[$field]);
			}
		}

		return $paginationParams;
	}
}
