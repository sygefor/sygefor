<?php

namespace Sygefor\Bundle\ActivityReportBundle\Service\Aggregation;

use Elastica\Aggregation\Terms;

/**
 * Nested aggregation to group participant summaries
 *
 *
 * @package Sygefor\Bundle\ActivityReportBundle\Service\Aggregation
 */
class TrainingCategoryAggregation extends AbstractTrainingTypeAggregation
{
    /**
     * constructor.
     */
    public function __construct($singleSessionTraining = true)
    {
        parent::__construct($singleSessionTraining);

        // add theme aggregation
        $theme = new Terms('training.category.source');
        $theme->setField($theme->getName());

        $this->addAggregation($theme);
    }
}