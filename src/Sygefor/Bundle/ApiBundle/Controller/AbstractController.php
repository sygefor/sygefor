<?php
namespace Sygefor\Bundle\ApiBundle\Controller;

use Elastica\Filter\Nested;
use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Elastica\Filter\Terms;
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
 * Class AbstractController
 * @package Sygefor\Bundle\ApiBundle\Controller
 */
abstract class AbstractController extends Controller
{
    /**
     * @var array
     */
    static protected $authorizedFields = array();

    /**
     * Protected function to help build authorized fields array
     * @param $source
     * @param string $prefix
     * @return array
     */
    static protected function buildAuthorizedFieldsArray($source, $prefix = "") {
        $array = array();
        foreach(static::$authorizedFields[$source] as $key) {
            $array[] = ($prefix ? $prefix.'.' : '').$key;
        }
        return $array;
    }

}
