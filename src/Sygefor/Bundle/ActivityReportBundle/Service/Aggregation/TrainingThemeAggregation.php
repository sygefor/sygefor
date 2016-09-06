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
class TrainingThemeAggregation extends AbstractTrainingTypeAggregation
{
    /**
     * constructor.
     */
    public function __construct($meeting = false)
    {
        parent::__construct($meeting);

        // add theme aggregation
        $theme = new Terms('training.theme.source');
        $theme->setField($theme->getName());

        $this->addAggregation($theme);
    }
}