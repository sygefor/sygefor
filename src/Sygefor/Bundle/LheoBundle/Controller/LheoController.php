<?php

namespace Sygefor\Bundle\LheoBundle\Controller;

use Elastica\Filter\Bool;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\Nested;
use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Elastica\Query;
use Elastica\Type;
use Sygefor\Bundle\LheoBundle\Writer\RdfWriter;
use Sygefor\Bundle\LheoBundle\Writer\XmlWriter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Sygefor\Bundle\CoreBundle\Entity\Organization;

/**
 * @Route("/api/lheo")
 */
class LheoController extends Controller
{
    /**
     * @Route("/{code}.rdf", requirements={"code" = "\w+"}, name="lheo.rdf")
     * @ParamConverter("organization", class="SygeforCoreBundle:Organization", options={"code" = "code"})
     * @Template()
     */
    public function rdfLheoAction(Organization $organization)
    {
        $rdfLheo = new RdfWriter();

        $results = $this->getTrainings($organization);
        $urfistCoordinates = $this->getUrfistInformations($organization);
        $lheo = $rdfLheo->generateLheoRdf($results, $urfistCoordinates);

        $response = new Response();
        $response->headers->set('Content-type', 'application/xml');
        $response->setContent($lheo);
        return $response;
    }

    /**
     * @Route("/{code}", requirements={"code" = "\w+"}, name="lheo.trainings")
     * @Template()
     * @ParamConverter("organization", class="SygeforCoreBundle:Organization", options={"code" = "code"})
     */
    public function xmlLheoAction(Organization $organization)
    {
        $xmlLheo = new XmlWriter();
        $degroupAction = isset($_GET['degroupAction']);
        $results = $this->getTrainings($organization);
        $urfistCoordinates = $this->getUrfistInformations($organization);
        $lheo = $xmlLheo->generateLheoXml($results, $urfistCoordinates, $degroupAction);

        $response = new Response();
        $response->headers->set('Content-type', 'application/xml');
        $response->setContent($lheo);
        return $response;
    }

    /**
     * Elasticsearch request to get trainings and nested sessions
     * @param Organization $organization
     * @return \Elastica\Result[]
     */
    public function getTrainings(Organization $organization)
    {
        /** @var Type $type */
        $type = $this->get('fos_elastica.index.sygefor3.training');

        // construct query
        $queryDSL = new Query();
        $query = new Query\MatchAll();
        $now = (new \DateTime("now", timezone_open('Europe/Paris')))->format('Y-m-d');

        // add filters
        $filters = new BoolAnd();
        $organization = new Term(array('organization.id' => $organization->getId()));
        $internship =   new Term(array('type' => 'internship'));
        $filters->addFilter($organization);
        $filters->addFilter($internship);

        $nestedFilter = new Nested();
        $nestedFilter->setPath('sessions');
        $nestedAnd = new BoolAnd();
        $displayOnline = new Term(array('sessions.displayOnline' => true));

        // return the training list with at least a session with a dateBegin > now but return all sessions
        $dateBegin = new Range('sessions.dateBegin', array("gte" => $now));
        $nestedAnd->addFilter($displayOnline);
		$nestedAnd->addFilter($dateBegin);
        $nestedFilter->setFilter($nestedAnd);
        $filters->addFilter($nestedFilter);

        // execute query with params
        $queryDSL->setQuery($query);
        $queryDSL->setFilter($filters);
        $results = $type->search($queryDSL);

        return $results->getResults();
    }

    /**
     * @param Organization $organization
     * @return mixed
     */
    public function getUrfistInformations(Organization $organization)
    {
        $pathJsonFile = file_get_contents("../src/Sygefor/Bundle/LheoBundle/Resources/config/urfistSpecificities.json");
        $urfistCoordinates = json_decode($pathJsonFile, true);
        $urfistCoordinates = $urfistCoordinates[$organization->getCode()];
        $urfistCoordinates['organization'] = $organization;
        return $urfistCoordinates;
    }
}