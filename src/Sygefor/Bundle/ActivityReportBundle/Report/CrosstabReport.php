<?php
namespace Sygefor\Bundle\ActivityReportBundle\Report;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Terms;
use Elastica\Filter\AbstractFilter;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Elastica\SearchableInterface;
use Sygefor\Bundle\ActivityReportBundle\Report\CrosstabReport\CrosstabFormatter;
use Sygefor\Bundle\ActivityReportBundle\Report\CrosstabReport\CrosstabReportAggregation;

/**
 * Class CrosstabReport
 * @package Sygefor\Bundle\ActivityReportBundle\Report
 */
class CrosstabReport
{
    /**
     * @var SearchableInterface $index
     */
    private $index;

    /**
     * @var boolean $debug
     */
    private $debug;

    /**
     * @var CrosstabFormatter
     */
    private $formatter;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var AbstractFilter
     */
    private $filter;

    /**
     * @var AbstractAggregation
     */
    private $aggregations;

    /**
     * @var array
     */
    private $terms= array();

    /**
     * @param SearchableInterface $index
     * @param AbstractFilter $filter
     */
    function __construct(SearchableInterface $index, AbstractFilter $filter = null)
    {
        $this->index = $index;
        $this->filter = $filter;
        $this->query = new Query();
        $this->formatter = new CrosstabFormatter();
    }

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param AbstractQuery $query
     */
    public function setQuery(AbstractQuery $query) {
        $this->query->setQuery($query);
    }

    /**
     * @param AbstractFilter $filter
     */
    public function setFilter(AbstractFilter $filter) {
        $this->filter = $filter;
    }

    /**
     * @return AbstractFilter
     */
    public function getFilter() {
        return $this->filter;
    }

    /**
     * @param AbstractAggregation $aggregation
     * @param array $terms
     * @param AbstractAggregation[] $subaggs
     *
     * @return AbstractAggregation
     */
    public function addAggregation($aggregation, $terms = array(), $subaggs = null)
    {
        // default : terms
        if(is_string($aggregation)) {
            $aggregation = new Terms($aggregation);
        } else {
            $aggregation = clone $aggregation;
        }

        $params = $aggregation->getParams();
        // some default config
//        if (method_exists($aggregation, 'setMinimumDocumentCount')) {
//            $aggregation->setMinimumDocumentCount(0);
//        }
        if (method_exists($aggregation, 'setSize') && empty($params['size'])) {
            $aggregation->setSize(0);
        }
        if (method_exists($aggregation, 'setField') && empty($params['field'])) {
            $aggregation->setField($aggregation->getName());
        }
        $this->aggregations[] = $aggregation;

        // add terms
        if($terms) {
            $this->terms[$aggregation->getName()] = $terms;
        }

        // add subaggs
        if($subaggs) {
            if(!is_array($subaggs)) {
                $subaggs = array($subaggs);
            }
            foreach($subaggs as $agg) {
                $aggregation->addAggregation($agg);
            }
        }

        return $aggregation;
    }

    /**
     * @return array
     */
    public function execute($inverse = false) {
        $query = clone $this->query;

        // empty result
        $query->setFields(array());
        $query->setSize(0);

        $last = null;
        $aggs = array_reverse($this->aggregations);
        foreach($aggs as $agg) {
            if($last) {
                $agg->addAggregation($last);
            }
            $last = $agg;
        }
        $agg = $last;

        // filter ?
        if($filter = $this->getFilter()) {
            $filtered = new Filter($agg->getName());
            $filtered->setFilter($filter);
            $filtered->addAggregation($agg);
            $agg = $filtered;
        }

        // add the final agg
        $query->addAggregation($agg);

        // get the result
        $rs = $this->index->search($query);
        $data = $rs->getAggregation($agg->getName());

        // add specific terms
        $this->formatter->setTerms($this->terms);
        $this->formatter->setDebug($this->debug);

        return $this->formatter->format($data, $inverse);
    }
}
