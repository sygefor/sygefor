<?php
/**
 * Created by PhpStorm.
 * User: Blaise
 * Date: 24/02/2016
 * Time: 13:04
 */

namespace Sygefor\Bundle\ActivityReportBundle\Service\Aggregation;


use Elastica\Aggregation\Terms;

class AbstractTrainingTypeAggregation extends Terms
{
    /**
     * constructor.
     */
    public function __construct($meeting = false)
    {
        parent::__construct('training.typeLabel.source');
        $this->setField($this->getName());
        $this->setMinimumDocumentCount(0);

        $method = $meeting ? "setInclude" : "setExclude";
        $this->$method("Rencontre scientifique");
    }

    /**
     * @return string
     */
    protected function _getBaseName()
    {
        return 'terms';
    }
}