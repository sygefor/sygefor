<?php

namespace ActivityReportBundle\Utils\ReportBuilder;

use Elastica\Query;
use Elastica\Search;
use Elastica\Filter\Terms;

/**
 * Class ListingReportBuilder.
 */
class ListingReportBuilder extends AbstractReportBuilder
{
    /**
     * @param $trainingTypes
     *
     * @return array
     */
    public function getReport($trainingTypes)
    {
        $search = $this->esIndex->getType('session');
        $filter = $this->getSessionFilter();
        if (count($trainingTypes)) {
            $filter->addFilter(new Terms('training.type', array_keys($trainingTypes)));
        }

        $query = new Query();
        $query->setFilter($filter);
        //$query->setSize(9999);

        $trainingKeys = ['id', 'name', 'theme', 'type', 'trainingOrganization', 'sponsor'];
        $sumKeys = ['hourNumber', 'numberOfRegistrations', 'numberOfParticipants', 'totalCost', 'totalTaking'];

        $scroll = $search->search($query, array(
            Search::OPTION_SEARCH_TYPE => Search::OPTION_SEARCH_TYPE_SCAN,
            Search::OPTION_SCROLL => '30s',
            Search::OPTION_SIZE => '500',
        ));

        $scrollId = $scroll->getResponse()->getScrollId();
        $total = $scroll->getTotalHits();
        $count = 0;
        $return = array();
        while ($count <= $total) {
            // get the data set for the given scroll id (the scroll id is valid 30 seconds)
            $response = $search->search(null, array(
                Search::OPTION_SCROLL_ID => $scrollId,
                Search::OPTION_SCROLL => '30s',
            ));
            $scrollId = $response->getResponse()->getScrollId();

            $results = $response->getResults();
            if (count($results) === 0) {
                break;
            }

            foreach ($results as $result) {
                $session = $result->getData();

                // reference the training
                $type = $session['training']['type'];
                $id = $session['training']['id'];
                $training = &$return[$type][$id];

                if (!$training) {
                    $training = array_intersect_key($session['training'], array_flip($trainingKeys));
                    $training['sessions'] = 0;
                }

                // sums
                $training['sessions'] += 1;
                foreach ($sumKeys as $key) {
                    if (isset($session[$key])) {
                        $training[$key] = isset($training[$key]) ? $training[$key] : 0;
                        $training[$key] += $session[$key];
                    }
                }

                // type d'intervenant
                if (in_array($type, array_keys($trainingTypes), true)) {
                    if (!empty($session['participations'])) {
                        $training['isOrganization'] = $session['participations'][0]['trainer']['isOrganization'];
                    }
                }
            }
        }

        $return = array_map(function ($list) {
            return array_values($list);
        }, $return);

        return $return;
    }
}
