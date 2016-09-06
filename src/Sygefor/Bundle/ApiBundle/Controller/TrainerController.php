<?php
namespace Sygefor\Bundle\ApiBundle\Controller;

use Elastica\Aggregation\GlobalAggregation;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\BoolNot;
use Elastica\Filter\Nested;
use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Elastica\Filter\Terms;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Search;
use Elastica\Type;
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
 * @Route("/api/trainer")
 */
class TrainerController extends AbstractController
{
    static protected $authorizedFields = array(
      'trainer' => array(
        'id',
        'fullName',
        'firstName',
        'lastName',
        'website',
        'status',
        'organization',
        'institution',
        'isAllowSendMail',
        'otherInstitution',
        'competenceFields',
        'responsabilities'
      )
    );

    /**
     * Trainer REST API
     * Get isPublic trainers
     * @Route("", name="api.trainer.public", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.trainer"}, serializerEnableMaxDepthChecks=true)
     */
    public function publicAction()
    {
        /** @var SearchService $search */
        $search = $this->get('sygefor_trainer.search');
        $search->setSize(999);

        // limit available source fields
        $search->setSource(array_merge(
            self::buildAuthorizedFieldsArray('trainer')
            //self::buildAuthorizedFieldsArray('training', 'training')
        ));

        // filter session by registration set by public
        $andFilter = new BoolAnd();
        $isPublicFilter = new Term(array('isPublic' => true));
        $isArchivedFilter = new BoolNot((new Term(array('isArchived' => true))));
        $andFilter->addFilter($isPublicFilter);
        $andFilter->addFilter($isArchivedFilter);
        $search->filterQuery($andFilter);

        // add trainings infos
        $results = $search->search();
        foreach($results['items'] as $key => $item) {
            $results['items'][$key]['trainings'] = $this->getTrainings($item['id']);
        }

        return $results;

    }

    /**
     * Trainer REST API
     * Get isUrfist trainers
     * @Route("/urfist", name="api.trainer.urfist", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.trainer"}, serializerEnableMaxDepthChecks=true)
     */
    public function urfistAction()
    {
        /** @var SearchService $search */
        $search = $this->get('sygefor_trainer.search');
        $search->setSize(999);

        // limit available source fields
        $search->setSource(array_merge(
            self::buildAuthorizedFieldsArray('trainer')
        //self::buildAuthorizedFieldsArray('training', 'training')
        ));

        // filter session by registration set by public
        $andFilter = new BoolAnd();
        $isUrfistFilter = new Term(array('isUrfist' => true));
//        $isPublicFilter = new Term(array('isPublic' => true));
        $isArchivedFilter = new BoolNot((new Term(array('isArchived' => true))));
        $andFilter->addFilter($isUrfistFilter);
//        $andFilter->addFilter($isPublicFilter);
        $andFilter->addFilter($isArchivedFilter);
        $search->filterQuery($andFilter);

        $search->addTermsAggregation('organizations', "organization.name");
        return $search->search();

    }

    /**
     * @param $trainerId
     * @return \Elastica\Result[]
     */
    private function getTrainings($trainerId) {
        /** @var SearchService $search */
        //$search = $this->get('sygefor_training.search');
        $search = new SearchService($this->get('fos_elastica.index.sygefor3.training'));
        $search->setSource(array('name', 'theme', 'sessions', 'organization'));

        $bool = new BoolAnd();

        // trainer id
        $bool->addFilter(new Term(array('sessions.participations.trainer.id' => $trainerId)));

        // the training must contain at least one session with public registration
        $bool->addFilter(new Term(array('sessions.displayOnline' => true)));

        $search->filterQuery($bool);
        $search->addSort('sessions.dateBegin', 'desc');
        $search->setSize(5);

        $results = $search->search();
        $items = $results['items'];

        foreach($items as $key => $item) {
            $sessions = $items[$key]['sessions'];
            $years = array();
            foreach($sessions as $session) {
                $years[] = $session['year'];
            }
            unset($items[$key]['sessions']);
            $years = array_unique($years);
            rsort($years);
            $items[$key]['years'] = $years;
        }

        return $items;
    }
}
