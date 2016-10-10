<?php

namespace Sygefor\Bundle\ActivityReportBundle\Service\Aggregation;

use Elastica\Aggregation\Filters;
use Elastica\Aggregation\Nested;
use Elastica\Filter\Bool;
use Elastica\Filter\Term;

/**
 * Nested aggregation to group trainers by type
 *
 * @package Sygefor\Bundle\ActivityReportBundle\Service\Aggregation
 */
class TrainerTypeAggregation extends Nested
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct('participations', 'participations');

        $filters = new Filters('isOrganization');

        // formateur urfist
        $bool = new Bool();
        $bool->addMust(new Term(array('isOrganization' => true)));
        $bool->addMust(new Term(array('isLocal' => true)));
        $filters->addFilter($bool, "Formateur interne");

        // formateur inter-urfist
        $bool = new Bool();
        $bool->addMust(new Term(array('isOrganization' => true)));
        $bool->addMust(new Term(array('isLocal' => false)));
        $filters->addFilter($bool, "Formateur inter-centre");

        // exterieur
        $filters->addFilter(new Term(array('isOrganization' => false)), "Formateur extérieur");

        $this->addAggregation($filters);
    }

    /**
     * @return string
     */
    protected function _getBaseName()
    {
        return 'nested';
    }
}