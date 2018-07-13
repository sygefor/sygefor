<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 11/7/17
 * Time: 3:10 PM.
 */

namespace ActivityReportBundle\Utils\ReportBuilder;

use Elastica\Query;
use Elastica\Index;
use Elastica\Filter\Ids;
use Elastica\Filter\Term;
use Elastica\Filter\BoolAnd;
use Elastica\Aggregation\Sum;
use Doctrine\ORM\EntityManager;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\ValueCount;
use AppBundle\Entity\Term\Training\Theme;
use Sygefor\Bundle\CoreBundle\Entity\Term\PresenceStatus;
use ActivityReportBundle\Utils\ReportBuilder\Crosstab\CrosstabReport;

/**
 * Class CrosstabReportBuilder.
 */
class CrosstabReportBuilder extends AbstractReportBuilder
{
    /**
     * @var array
     */
    protected $terms;

    /**
     * CrosstabReportBuilder constructor.
     *
     * @param Index         $esIndex
     * @param array         $sessionIds
     * @param EntityManager $em
     */
    public function __construct(Index $esIndex, $sessionIds, EntityManager $em)
    {
        parent::__construct($esIndex, $sessionIds);
        $this->terms = $this->getTermValues($em);
    }

    /**
     * @param $trainingTypes
     *
     * @return array
     */
    public function getReport($trainingTypes)
    {
        $return = array();
        $typedAggregations = [
            'session' => [
                'esFilters' => $this->getSessionFilter(),
                'aggregations' => [
                    'type' => [
                        'entity' => 'type',
                        'field' => 'training.typeLabel.source',
                        'terms' => [
                            [
                                'name' => 'theme_type',
                                'field' => 'training.theme.name.source',
                            ],
                            [
                                'name' => 'place_type',
                                'field' => 'place.source',
                            ],
                            [
                                'name' => 'totalCost_type',
                                'class' => Sum::class,
                                'field' => 'totalCost',
                                'inverse' => false,
                            ],
                            [
                                'name' => 'totalTaking_type',
                                'class' => Sum::class,
                                'field' => 'totalTaking',
                                'inverse' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $presentInscriptionFilters = $this->getSessionFilter('session.id');
        $presentInscriptionFilters->addFilter((new Term(array('presenceStatus.status' => PresenceStatus::STATUS_PRESENT))));
        $presentInscriptionFilters = $this->getSessionFilter('session.id');

        $typedAggregations = array_merge($typedAggregations, [
            'inscription' => [
                'esType' => 'inscription',
                'esFilters' => $presentInscriptionFilters,
                'aggregations' => [
                    [
                        'field' => 'session.training.organization.name.source',
                        'terms' => [
                            [
                                'name' => 'inscription_hours',
                                'class' => Sum::class,
                                'field' => 'session.hourNumber',
                                'inverse' => false,
                            ],
                            [
                                'name' => 'inscription_organization',
                                'class' => ValueCount::class,
                                'field' => 'session.id',
                                'inverse' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // considerationPrestationType_considerationPrestationHour
        // for earch es type
        foreach ($typedAggregations as $esType => $options) {
            if (isset($options['esType'])) {
                $esType = $options['esType'];
            }

            // for each 1st aggregated field
            foreach ($options['aggregations'] as $aggregationClass => $aggregationOptions) {
                $esField = $aggregationOptions['field'];
                $entityName = isset($aggregationOptions['entity']) ? $aggregationOptions['entity'] : null;

                // create new crosstab
                $crosstab = new CrosstabReport($this->esIndex->getType($esType), $options['esFilters']);
                if ($entityName && isset($this->terms[$entityName])) {
                    $crosstab->setTerms($entityName, $this->terms[$entityName]);
                }

                // for each 2nd aggregated field
                $aggregationClass = class_exists($aggregationClass) ? $aggregationClass : Terms::class;
                foreach ($aggregationOptions['terms'] as $aggregationTermOptions) {
                    $aggregationTermClass = isset($aggregationTermOptions['class']) ? $aggregationTermOptions['class'] : Terms::class;
                    $aggregationTermField = isset($aggregationTermOptions['field']) ? $aggregationTermOptions['field'] : $aggregationOptions['field'];
                    $aggregation = $this->getAggregation($esField, $aggregationClass, $aggregationTermClass, $aggregationTermField);
                    $inverse = isset($aggregationTermOptions['inverse']) ? $aggregationTermOptions['inverse'] : true;
                    $_crosstab = clone $crosstab;
                    $_crosstab->addAggregation($aggregation);
                    $return[$aggregationTermOptions['name']] = $_crosstab->execute($inverse);
                }
            }
        }

        return $return;
    }

    /**
     * @param EntityManager $em
     *
     * @return array
     */
    protected function getTermValues(EntityManager $em)
    {
        $termClasses = [
            'theme' => Theme::class,
        ];

        $terms = array();
        foreach ($termClasses as $key => $class) {
            $terms[$key] = $this->getSortedTerms($em, $class);
        }

        $terms['type'] = [
            'Stage',
        ];

        return $terms;
    }

    /**
     * Return a sorted list of terms.
     *
     * @param EntityManager $em
     * @param $class
     *
     * @return array
     */
    protected function getSortedTerms(EntityManager $em, $class)
    {
        $repo = $em->getRepository($class);
        $terms = $repo->findBy(array(), array($class::orderBy() => 'ASC'));
        $terms = array_map(function ($type) {
            return (string) $type;
        }, $terms);

        return array_combine($terms, $terms);
    }

    /**
     * Construct an elasticsearch aggregation.
     *
     * @param $field
     * @param string $aggregationClass
     * @param string $termClass
     * @param string $aggregationTermField
     *
     * @return Terms
     */
    protected function getAggregation($field, $aggregationClass, $termClass, $aggregationTermField)
    {
        /** @var Terms $agg */
        $agg = new $aggregationClass($field);
        $agg->setField($agg->getName());
        if (method_exists($agg, 'setSize')) {
            $agg->setSize(999999);
        }

        /** @var Terms $term */
        $term = new $termClass($aggregationTermField, $aggregationTermField);
        $term->setField($term->getName());
        if (method_exists($term, 'setSize')) {
            $term->setSize(999999);
        }

        $agg->addAggregation($term);

        return $agg;
    }

    /**
     * Get a filtered query based on filtered sessions by id.
     *
     * @param $filter
     *
     * @return BoolAnd
     */
    protected function getInscritsTraineeFilter($filter = null)
    {
        // construct query
        $query = new Query();
        $filters = new BoolAnd();
        $query->setFields(array('trainee.id'));
        $query->setSize(999999);
        $filters->addFilter(new \Elastica\Filter\Terms('session.id', $this->sessionIds));
        $filters->addFilter(new Term(array('presenceStatus.status' => PresenceStatus::STATUS_PRESENT)));
        if ($filter) {
            $filters->addFilter($filter);
        }
        $query->setPostFilter($filters);

        // get present trainee ids
        $results = $this->esIndex->getType('inscription')->search($query)->getResults();
        $trainees = array();
        foreach ($results as $result) {
            if (!in_array($result->getHit()['fields']['trainee.id'][0], $trainees)) {
                $trainees[] = $result->getHit()['fields']['trainee.id'][0];
            }
        }

        // return elasticsearch filter
        $filter = new BoolAnd();
        $filter->addFilter(new Ids('trainee', $trainees));

        return $filter;
    }
}
