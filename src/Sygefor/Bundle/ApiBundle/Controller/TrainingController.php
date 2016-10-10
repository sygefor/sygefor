<?php

namespace Sygefor\Bundle\ApiBundle\Controller;

use Elastica\Filter\BoolOr;
use Elastica\Filter\Term;
use Elastica\Search;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/api/training")
 */
class TrainingController extends AbstractController
{
    static protected $authorizedFields = array(
        'session' => array(
          'id',
          'name',
          'dateBegin',
          'dateEnd',
          'year',
          'semester',
          'semesterLabel',
          'limitRegistrationDate',
          'hourNumber',
          'dayNumber',
          'numberOfParticipants',
          'price',
          'maximumNumberOfRegistrations',
          'place',
          'registration',
          'displayOnline',
          'availablePlaces',
          'participations',
          'schedule',
          'promote',
          'status',
        ),
        'training' => array(
          'id',
          'type',
          'typeLabel',
          'typeLabel.source',
          'organization',
          'number',
          'serial',
          'theme',
          'tags',
          'name',
          'description',
          'program',
          'prerequisite',
          'interventionType',
          'publicType',
          'firstSessionPeriodSemester',
          'firstSessionPeriodYear',
        ),
    );

    /**
     * @Route("", name="api.training.search", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.training"}, serializerEnableMaxDepthChecks=true)
     */
    public function trainingSearchAction(Request $request)
    {
        /** @var SearchService $search */
        $search = $this->get('sygefor_training.search');
        $search->handleRequestBody($request);

        // limit available source fields
        $search->setSource(array_merge(
            self::buildAuthorizedFieldsArray('training'),
            self::buildAuthorizedFieldsArray('session', 'sessions')
        ));

        // the training must contain at least one session online displayed
        $filter = new Term(array('sessions.displayOnline' => true));
        $search->filterQuery($filter);

        return $search->search();
    }

    /**
     * @Route("/session", name="api.training.session.search", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.training"}, serializerEnableMaxDepthChecks=true)
     */
    public function sessionSearchAction(Request $request)
    {
        /** @var SearchService $search */
        $search = $this->get('sygefor_training.session.search');
        $search->handleRequestBody($request);
        $includePrivate = (bool) $request->query->get('private');

        // limit available source fields
        $search->setSource(array_merge(
          self::buildAuthorizedFieldsArray('session'),
          self::buildAuthorizedFieldsArray('training', 'training')
        ));

        // filter session by displayOnline
        $onlineFilter = new Term(array('displayOnline' => true));

        // include private sessions
        if ($includePrivate) {
            $orFilter      = new BoolOr();
            $privateFilter = new Term(array('registration' => AbstractSession::REGISTRATION_PRIVATE));
            $orFilter->addFilter($onlineFilter);
            $orFilter->addFilter($privateFilter);
            $search->filterQuery($orFilter);
        }
        else {
            $search->filterQuery($onlineFilter);
        }

        return $search->search();
    }

    /**
     * Training REST API.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="api.training.detail", defaults={"_format" = "json"})
     * @ParamConverter("training", class="SygeforTrainingBundle:Training\AbstractTraining", options={"id" = "id"})
     * @Rest\View(serializerGroups={"api", "api.training"}, serializerEnableMaxDepthChecks=true)
     */
    public function trainingAction(AbstractTraining $training)
    {
        // only training with a online displayed session
        /** @var AbstractSession $session */
        foreach ($training->getSessions() as $session) {
            if ($session->isDisplayOnline() || $session->getRegistration() === AbstractSession::REGISTRATION_PRIVATE) {
                return $training;
            }
        }
        throw new NotFoundHttpException();
    }
}
