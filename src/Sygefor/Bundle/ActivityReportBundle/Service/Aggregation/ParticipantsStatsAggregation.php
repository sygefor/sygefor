<?php

namespace Sygefor\Bundle\ActivityReportBundle\Service\Aggregation;

use Elastica\Aggregation\Filters;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Sum;
use Elastica\Aggregation\Terms;
use Elastica\Filter\Bool;
use Elastica\Filter\Term;

/**
 * Nested aggregation to group participant summaries
 *
 *
 * @package Sygefor\Bundle\ActivityReportBundle\Service\Aggregation
 */
class ParticipantsStatsAggregation extends AbstractTrainingTypeAggregation
{
    /**
     * constructor.
     */
    public function __construct($meeting = false)
    {
        parent::__construct($meeting);

        // prepare sum
        $sum = new Sum('sum');
        $sum->setField('count');

        // sum by public type
        $public = new Terms('publicType');
        $public->setField($public->getName());
        $public->addAggregation($sum);

        // sum overall
        $nested = new Nested('summary', 'participantsStats');
        $nested->addAggregation($sum);
        $nested->addAggregation($public);

        $this->addAggregation($nested);
    }

}