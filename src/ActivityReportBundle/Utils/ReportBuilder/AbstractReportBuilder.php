<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 11/7/17
 * Time: 3:32 PM.
 */

namespace ActivityReportBundle\Utils\ReportBuilder;

use Elastica\Query;
use Elastica\Index;
use Elastica\Filter\Ids;
use Elastica\Filter\Terms;
use Elastica\Filter\BoolAnd;

/**
 * Class AbstractReportBuilder.
 */
abstract class AbstractReportBuilder
{
    /**
     * @var Index
     */
    protected $esIndex;

    /**
     * @var array
     */
    protected $sessionIds;

    /**
     * CrosstabReportBuilder constructor.
     *
     * @param Index $index
     * @param array $sessionIds
     */
    public function __construct(Index $index, $sessionIds)
    {
        $this->esIndex = $index;
        $this->sessionIds = $sessionIds;
    }

    /**
     * @param $trainingTypes
     *
     * @return mixed
     */
    abstract public function getReport($trainingTypes);

    /**
     * Get a filtered query based on filtered sessions by id.
     *
     * @param $field string
     *
     * @return BoolAnd
     */
    protected function getSessionFilter($field = null)
    {
        $filter = new BoolAnd();
        if ($field) {
            $filter->addFilter(new Terms($field, $this->sessionIds));
        } else {
            $filter->addFilter(new Ids('session', $this->sessionIds));
        }

        return $filter;
    }
}
