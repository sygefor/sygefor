<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 11/7/17
 * Time: 3:09 PM.
 */

namespace ActivityReportBundle\Utils\ReportBuilder;

use Elastica\Query;
use Elastica\Filter\Term;
use Elastica\Filter\Terms;
use Elastica\Aggregation\Sum;
use Elastica\Filter\AbstractFilter;

/**
 * Class SummariesReportBuilder.
 */
class SummaryReportBuilder extends AbstractReportBuilder
{
    /**
     * @param $trainingTypes
     *
     * @return array
     */
    public function getReport($trainingTypes)
    {
        $return = array();
        foreach (array_keys($trainingTypes) as $type) {
            $return[$type] = $this->getSummary($type);
        }
        if (count($trainingTypes) > 1) {
            $return['all'] = $this->getSummary(array_keys($trainingTypes));
        }

        return $return;
    }

    /**
     * Summary.
     */
    protected function getSummary($type = null)
    {
        $return = array();

        $sessionfilter = $this->getSessionFilter();
        if ($type) {
            if (is_array($type)) {
                $sessionfilter->addFilter(new Terms('training.type', $type));
            } else {
                $sessionfilter->addFilter(new Term(array('training.type' => $type)));
            }
        }

        $trainingFilter = $this->getSessionFilter('sessions.id');
        if ($type) {
            if (is_array($type)) {
                $trainingFilter->addFilter(new Terms('type', $type));
            } else {
                $trainingFilter->addFilter(new Term(array('type' => $type)));
            }
        }

        $sums = $this->getSums($sessionfilter, [
            'numberOfRegistrations',
            'numberOfParticipants',
            'hourNumber',
            'costs',
            'considerationCosts',
            'totalCost',
            'totalTaking',
        ]);

        // Nombre d'événements
        $_query = new Query();
        $_query->setFilter($trainingFilter);
        $return['trainings'] = $this->esIndex->getType('training')->count($_query);

        // Nombre de sessions
        $query = new Query();
        $query->setFilter($sessionfilter);
        $return['sessions'] = $this->esIndex->getType('session')->count($query);

        // Nombre d'heures de formation
        $return['hours'] = $sums['hourNumber'];

        // Nombre de personnes formées
        $return['participations'] = $sums['numberOfParticipants'];

        // Coût global
        $return['totalCost'] = $sums['totalCost'];

        // Recettes globales
        $return['taking'] = $sums['totalTaking'];

        // Nombre d'heures / personnes
        $return['hours_participant'] = $sums['numberOfParticipants'] > 0 ? $sums['hourNumber'] / $sums['numberOfParticipants'] : 0;

        // Nombre de demande d'inscription
        $return['registrations'] = $sums['numberOfRegistrations'];

        return $return;
    }

    /**
     * @return int
     */
    protected function getSums(AbstractFilter $filter, $fields, $type = 'session')
    {
        $query = $this->getAggregableQuery($filter);
        foreach ($fields as $field) {
            $sum = new Sum($field);
            $sum->setField($field);
            $query->addAggregation($sum);
        }
        $rs = $this->esIndex->getType($type)->search($query);
        $return = array();
        foreach ($fields as $field) {
            $return[$field] = $rs->getAggregation($field)['value'];
        }

        return $return;
    }

    /**
     * @return Query
     */
    protected function getAggregableQuery(AbstractFilter $filter)
    {
        $query = new Query();
        $query->setQuery(new Query\Filtered(null, $filter));
        $query->setFields(array());
        $query->setSize(0);

        return $query;
    }
}
