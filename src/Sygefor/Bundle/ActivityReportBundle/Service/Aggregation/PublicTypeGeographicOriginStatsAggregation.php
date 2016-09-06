<?php

namespace Sygefor\Bundle\ActivityReportBundle\Service\Aggregation;

use Elastica\Aggregation\Filters;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Sum;
use Elastica\Aggregation\Terms;
use Elastica\Filter\Bool;
use Elastica\Filter\Term;

/**
 * Nested aggregation to group participant Geographic Origins
 *
 *
 * @package Sygefor\Bundle\ActivityReportBundle\Service\Aggregation
 */
class PublicTypeGeographicOriginStatsAggregation extends Nested
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct('summary', 'participantsStats');

        // prepare sum
        $sum = new Sum('sum');
        $sum->setField('count');

        // sum by geographic origin
        $origin = new Terms('geographicOrigin');
        $origin->setField($origin->getName());
        $origin->addAggregation($sum);

        // sum by publicCategory
        $category = new Terms('publicCategory');
        $category->setField($category->getName());
        $category->addAggregation($sum);
        $category->addAggregation($origin);

        $this->addAggregation($category);
    }

    /**
     * @return string
     */
    protected function _getBaseName()
    {
        return 'nested';
    }
}