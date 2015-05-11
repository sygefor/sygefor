<?php
namespace Sygefor\Bundle\TrainingBundle\Controller;

use Elastica\Filter\Nested;
use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Elastica\Search;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\TrainingBundle\Entity\DiverseTraining;
use Sygefor\Bundle\TrainingBundle\Entity\Internship;
use Sygefor\Bundle\TrainingBundle\Entity\Meeting;
use Sygefor\Bundle\TrainingBundle\Entity\SingleSessionTraining;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\TrainingCourse;
use Sygefor\Bundle\TrainingBundle\Form\DiverseTrainingType;
use Sygefor\Bundle\TrainingBundle\Form\InternshipType;
use Sygefor\Bundle\TrainingBundle\Form\MeetingType;
use Sygefor\Bundle\TrainingBundle\Form\SessionType;
use Sygefor\Bundle\TrainingBundle\Form\TrainingCourseType;
use Sygefor\Bundle\TrainingBundle\Form\TrainingType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\SecurityContext;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Route("/api/training")
 */
class ApiController extends Controller
{
    static $authorizedFields = array(
        'session' => array(
          'id',
          'dateBegin',
          'dateEnd',
          'year',
          'semester',
          'semesterLabel',
          'limitRegistrationDate',
          'hourDuration',
          'numberOfParticipants',
          'place',
          'registration',
          'availablePlaces',
          'promote'
        ),
        'training' => array(
          'id',
          'type',
          'typeLabel',
          'organization',
          'number',
          'serial',
          'theme',
          'tags',
          'name',
          'objectives',
          'program'
        )
    );

    /**
     * @Route("", name="api.training.search", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api"}, serializerEnableMaxDepthChecks=true)
     */
    public function trainingSearchAction(Request $request)
    {
        /** @var SearchService $search */
        $search = $this->get('sygefor_training.search');
        $search->handleRequestBody($request);

        // the training must contain at least one session with public registration
        $filter = new Term(array('sessions.registration' => Session::REGISTRATION_PUBLIC));
        $search->filterQuery($filter);

        // limit available source fields
        $search->setSource(array_merge(
            self::buildAuthorizedFieldsArray('training'),
            self::buildAuthorizedFieldsArray('session', 'sessions')
          ));

        return $search->search();
    }

    /**
     * @Route("/session", name="api.training.session.search", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api"}, serializerEnableMaxDepthChecks=true)
     */
    public function sessionSearchAction(Request $request)
    {
        /** @var SearchService $search */
        $search = $this->get('sygefor_training.session.search');
        $search->handleRequestBody($request);


        // limit available source fields
        $search->setSource(array_merge(
          self::buildAuthorizedFieldsArray('session'),
          self::buildAuthorizedFieldsArray('training', 'training')
        ));

        // filter session by registration set by public
        $filter = new Term(array('registration' => Session::REGISTRATION_PUBLIC));
        $search->filterQuery($filter);

        return $search->search();
    }

    /**
     * Training REST API
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="api.training.detail", defaults={"_format" = "json"})
     * @ParamConverter("training", class="SygeforTrainingBundle:Training", options={"id" = "id"})
     * @Rest\View(serializerGroups={"api"}, serializerEnableMaxDepthChecks=true)
     */
    public function trainingAction(Training $training)
    {
        // we remove all the private sessions
        // todo : serilizer listener ?
        $sessions = $training->getSessions();
        /** @var Session $session */
        foreach($sessions as $session) {
            if($session->getRegistration() < Session::REGISTRATION_PUBLIC) {
                $sessions->removeElement($session);
            }
        }

        // if there is no session, not found
        if(count($sessions) == 0) {
            throw new NotFoundHttpException("Not found.");
        }

        // set the new sessions array and return
        return $training;
    }

    /**
     * Private function to help build authorized fields array
     * @param $source
     * @param string $prefix
     * @return array
     */
    static function buildAuthorizedFieldsArray($source, $prefix = "") {
        $array = array();
        foreach(self::$authorizedFields[$source] as $key) {
            $array[] = ($prefix ? $prefix.'.' : '').$key;
        }
        return $array;
    }

}
