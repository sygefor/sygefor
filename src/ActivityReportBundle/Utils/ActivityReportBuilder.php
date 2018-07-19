<?php

namespace ActivityReportBundle\Utils;

use Elastica\Query;
use Elastica\Index;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use ActivityReportBundle\Utils\ReportBuilder\ListingReportBuilder;
use ActivityReportBundle\Utils\ReportBuilder\SummaryReportBuilder;
use ActivityReportBundle\Utils\ReportBuilder\CrosstabReportBuilder;

/**
 * Class ActivityReportBuilder.
 */
class ActivityReportBuilder
{
    /**
     * @var Index
     */
    protected $esIndex;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $sessionIds;

    /**
     * @param Index         $esIndex
     * @param EntityManager $em
     * @param Request       $request
     */
    public function __construct(Index $esIndex, EntityManager $em, Request $request)
    {
        $this->esIndex = $esIndex;
        $this->em = $em;
        $this->sessionIds = $this->getSessionIds($esIndex, $request);
    }

    /**
     * @param $trainingTypes
     *
     * @return array
     */
    public function getSummaries($trainingTypes)
    {
        $summaryReporBuilder = new SummaryReportBuilder($this->esIndex, $this->sessionIds);

        return $summaryReporBuilder->getReport($trainingTypes);
    }

    /**
     * @param $trainingTypes
     *
     * @return array
     */
    public function getCrosstabs($trainingTypes)
    {
        $crosstabReporBuilder = new CrosstabReportBuilder($this->esIndex, $this->sessionIds, $this->em);

        return $crosstabReporBuilder->getReport($trainingTypes);
    }

    /**
     * @param $trainingTypes
     *
     * @return array
     */
    public function getListing($trainingTypes)
    {
        $listingReporBuilder = new ListingReportBuilder($this->esIndex, $this->sessionIds);

        return $listingReporBuilder->getReport($trainingTypes);
    }

    /**
     * @param Index   $esIndex
     * @param Request $request
     *
     * @return array
     */
    protected function getSessionIds(Index $esIndex, Request $request)
    {
        // query
        $query = new Query();
        $httpQuery = $request->request->all();
        $query->setRawQuery($httpQuery);

        // the current query is converted to session list to easier queries
        $query->setFields(array());
        $query->setSize(9999999);
        $rs = $esIndex->getType('session')->search($query);
        $sessionIds = array();
        foreach ($rs->getResults() as $result) {
            $sessionIds[] = $result->getId();
        }

        return $sessionIds;
    }
}
