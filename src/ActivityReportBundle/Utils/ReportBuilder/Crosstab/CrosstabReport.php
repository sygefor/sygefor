<?php

namespace ActivityReportBundle\Utils\ReportBuilder\Crosstab;

use Elastica\Query;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\Filter;
use Elastica\SearchableInterface;
use Elastica\Filter\AbstractFilter;
use Elastica\Aggregation\AbstractAggregation;

/**
 * Class CrosstabReport.
 */
class CrosstabReport
{
    /**
     * @var SearchableInterface
     */
    private $index;

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
    private $terms = array();

    /**
     * @param SearchableInterface $index
     * @param AbstractFilter      $filter
     */
    public function __construct(SearchableInterface $index, AbstractFilter $filter = null)
    {
        $this->index = $index;
        $this->filter = $filter;
        $this->query = new Query();
        $this->formatter = new CrosstabFormatter();
    }

    /**
     * @param AbstractAggregation $aggregation
     *
     * @return AbstractAggregation
     */
    public function addAggregation($aggregation)
    {
        // default : terms
        if (is_string($aggregation)) {
            $aggregation = new Terms($aggregation);
        } else {
            $aggregation = clone $aggregation;
        }

        $params = $aggregation->getParams();
        if (method_exists($aggregation, 'setSize') && empty($params['size'])) {
            $aggregation->setSize(0);
        }
        if (method_exists($aggregation, 'setField') && empty($params['field'])) {
            $aggregation->setField($aggregation->getName());
        }

        $this->aggregations[] = $aggregation;
    }

    /**
     * @param AbstractFilter $filter
     */
    public function setFilter(AbstractFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param array $terms
     */
    public function setTerms($key, $terms)
    {
        $this->terms[$key] = $terms;
    }

    /**
     * @return array
     */
    public function execute($inverse = false)
    {
        $query = clone $this->query;

        // empty result
        $query->setFields(array());
        $query->setSize(0);

        $last = null;
        $aggs = array_reverse($this->aggregations);
        foreach ($aggs as $agg) {
            if ($last) {
                $agg->addAggregation($last);
            }
            $last = $agg;
        }
        $agg = $last;

        // filter query
        if ($filter = $this->filter) {
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

        return $this->formatter->format($data, $inverse);
    }
}
