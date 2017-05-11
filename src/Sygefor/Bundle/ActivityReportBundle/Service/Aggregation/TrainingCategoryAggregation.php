<?php

namespace Sygefor\Bundle\ActivityReportBundle\Service\Aggregation;

use Elastica\Aggregation\Terms;

/**
 * Nested aggregation to group participant summaries
 */
class TrainingCategoryAggregation extends AbstractTrainingTypeAggregation
{
    /**
     * constructor.
     */
    public function __construct($meeting = false)
    {
        parent::__construct($meeting);

        // add theme aggregation
        $theme = new Terms('training.category.source');
        $theme->setField($theme->getName());

        $this->addAggregation($theme);
    }
}