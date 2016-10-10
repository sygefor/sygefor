<?php

namespace Sygefor\Bundle\ActivityReportBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Sum;
use Elastica\Filter\AbstractFilter;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\BoolNot;
use Elastica\Filter\Exists;
use Elastica\Filter\Ids;
use Elastica\Filter\Term;
use Elastica\Filter\Terms;
use Elastica\Index;
use Elastica\Query;
use Elastica\Search;
use Sygefor\Bundle\ActivityReportBundle\Report\CrosstabReport;
use Sygefor\Bundle\ActivityReportBundle\Service\Aggregation\ParticipantsStatsAggregation;
use Sygefor\Bundle\ActivityReportBundle\Service\Aggregation\PublicTypeGeographicOriginStatsAggregation;
use Sygefor\Bundle\ActivityReportBundle\Service\Aggregation\TrainerTypeAggregation;
use Sygefor\Bundle\ActivityReportBundle\Service\Aggregation\TrainingCategoryAggregation;
use Sygefor\Bundle\ActivityReportBundle\Service\Aggregation\TrainingThemeAggregation;
use Sygefor\Bundle\InstitutionBundle\Entity\Term\GeographicOrigin;
use Sygefor\Bundle\TraineeBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Theme;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\TrainingCategory;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ActivityReportBuilder
 * @package Sygefor\Bundle\ActivityReportBundle\Service
 */
class ActivityReportBuilder
{
    /**
     * @var Index
     */
    protected $index;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var array
     */
    protected $sessionIds;

    /**
     * @var array
     */
    protected $terms;

    /**
     * @param Index $index
     */
    public function __construct(Index $index, EntityManager $em, Request $request)
    {
        // doctrine
        $this->em = $em;

        // index
        $this->index = $index;

        // query
        $this->query = new Query();
        $query = $request->request->all();
        $this->query->setRawQuery($query);

        // the current query is converted to session list to easier queries
        $query = clone $this->query;
        $query->setFields(array());
        $query->setSize(9999999);
        $rs = $this->index->getType('session')->search($query);
        $this->sessionIds = array();
        foreach($rs->getResults() as $result) {
            $this->sessionIds[] = $result->getId();
        }

        // some usefull terms
        $this->terms['theme'] = $this->getSortedTerms(Theme::class);
        $this->terms['geographic_origin'] = $this->getSortedTerms(GeographicOrigin::class);
        $this->terms['disciplinary'] = $this->getSortedTerms(Disciplinary::class, array('parent' => null));
        $this->terms['public_type'] = $this->getSortedTerms(PublicType::class);
        $this->terms['category'] = $this->getSortedTerms(TrainingCategory::class);
    }

    /**
     * Return a sorted list of terms
     */
    protected function getSortedTerms($class, array $criteria = array())
    {
        $repo = $this->em->getRepository($class);
        $terms = $repo->findBy($criteria, array('position' => 'ASC'));
        $terms = array_map(function($type) { return $type->getName(); }, $terms);

        return array_combine($terms, $terms);
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

    /**
     * Get a filtered query based on filtered sessions by id
     *
     * @param $field string     Specify a field where session.id could be found, if query will be performed on another type
     *
     * @return BoolAnd
     */
    protected function getSessionFilter($field = null) {
        $filter = new BoolAnd();
        if($field) {
            $filter->addFilter(new Terms($field, $this->sessionIds));
        } else {
            $filter->addFilter(new Ids("session", $this->sessionIds));
        }
        return $filter;
    }

    /**
     * @return int
     */
    protected function getSums(AbstractFilter $filter, $fields, $type = 'session')
    {
        $query = $this->getAggregableQuery($filter);
        foreach($fields as $field) {
            $sum = new Sum($field);
            $sum->setField($field);
            $query->addAggregation($sum);
        }
        $rs = $this->index->getType($type)->search($query);
        $return = array();
        foreach($fields as $field) {
            $return[$field] = $rs->getAggregation($field)['value'];
        }
        return $return;
    }

    /**
     * Summary
     */
    protected function getSummary($type = null)
    {
        $return = array();

        $sessionfilter = $this->getSessionFilter();
        if($type) {
            if(is_array($type)) {
                $sessionfilter->addFilter(new Terms('training.type', $type));
            } else {
                $sessionfilter->addFilter(new Term(array('training.type' => $type)));
            }
        }


        $trainingFilter = $this->getSessionFilter('sessions.id');
        if($type) {
            if(is_array($type)) {
                $trainingFilter->addFilter(new Terms('type', $type));
            } else {
                $trainingFilter->addFilter(new Term(array('type' => $type)));
            }
        }

        $sums = $this->getSums($sessionfilter, array("numberOfRegistrations", "numberOfParticipants", "totalCost", "hourDuration", "totalTaking"));

        // Nombre d'événements
        $_query = new Query();
        $_query->setFilter($trainingFilter);
        $return['trainings'] = $this->index->getType('training')->count($_query);

        // Nombre de sessions
        $query = new Query();
        $query->setFilter($sessionfilter);
        $return['sessions'] = $this->index->getType('session')->count($query);

        // Nombre d'heures de formation
        $return['hours'] = $sums['hourDuration'];

        // Nombre de personnes formées
        $return['participations'] = $sums['numberOfParticipants'];

        // Coût global
        $return['cost'] = $sums['totalCost'];

        // Recettes globales
        $return['taking'] = $sums['totalTaking'];

        // specific : stages
        if($type == 'internship') {

            // Nombre d'heures / personnes
            $return['hours_participant'] = $sums['numberOfParticipants'] > 0 ? $sums['hourDuration'] / $sums['numberOfParticipants'] : 0;

            // Nombre de demande d'inscription
            $return['registrations'] = $sums['numberOfRegistrations'];
        }

        // specific : all trainings
        if (is_array($type)) {
            $query = $this->getAggregableQuery($sessionfilter);
            $agg = new \Elastica\Aggregation\Terms("training.externInitiative");
            $agg->setField("training.externInitiative");
            $query->addAggregation($agg);
            $rs = $this->index->getType('session')->search($query)->getAggregation("training.externInitiative");
            foreach($rs['buckets'] as $bucket) {
                $return['external_'.$bucket['key']] = $bucket['doc_count'];
            }
        }

        return $return;
    }

    /**
     * Lists
     */
    public function getListing($types = array(), $options = array())
    {
        $search = $this->index->getType('session');
        $filter = $this->getSessionFilter();
        if(count($types)) {
            $filter->addFilter(new Terms('training.type', array_keys($types)));
        }

        $query = new Query();
        $query->setFilter($filter);
        //$query->setSize(9999);

        $trainingKeys = (isset ($options['trainingKeys'])&& is_array($options['trainingKeys'])) ? $options['trainingKeys'] : array('id', 'name', 'theme', 'eventKind', 'eventType', 'publicTypes', 'teachingCursus', 'disciplinary', 'organism', 'pedagogicPartner', 'otherPartner');
        $sumKeys = (isset ($options['sumKeys'])&& is_array($options['sumKeys'])) ? $options['sumKeys'] : array('hourDuration', 'publicTypes', 'numberOfRegistrations', 'numberOfParticipants', 'totalCost');

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
            $response = $search->search(null ,array(
                Search::OPTION_SCROLL_ID => $scrollId,
                Search::OPTION_SCROLL => '30s'
              ));
            $scrollId = $response->getResponse()->getScrollId();

            $results = $response->getResults();
            if (count($results) == 0) {
                break;
            }

            foreach ($results as $result) {
                $session = $result->getData();

                // reference the training
                $type = $session['training']['type'];
                $id = $session['training']['id'];
                $training = &$return[$type][$id];

                if(!$training) {
                    $training = array_intersect_key($session['training'], array_flip($trainingKeys));
                    $training['sessions'] = 0;
                    if ($type == "meeting") {
                        $training['session.date'] = $session['dateBegin'];
                    }
                }

                // sums
                $training['sessions'] += 1;
                foreach($sumKeys as $key) {
                    if(isset($session[$key])) {
                        $training[$key] = isset($training[$key]) ? $training[$key] : 0;
                        $training[$key] += $session[$key];
                    }
                }

                // type d'intervenant
                if (in_array($type, array_keys($types))) {
                    if(!empty($session['participations'])) {
                        $training['isOrganization'] = $session['participations'][0]['trainer']['isOrganization'];
                    }
                }
            }
        }

        $return = array_map(function($list) { return array_values($list); }, $return);
        return $return;
    }

    /**
     * Get summaries
     */
    public function getSummaries($types)
    {
        $return = array();
        foreach (array_keys($types) as $type) {
            $return[$type] = $this->getSummary($type);
        }
        if (count($types) > 1) {
            $return['all'] = $this->getSummary(array_keys($types));
        }
        return $return;
    }

    /**
     * Get training crosses
     * @param $multipleSessionTrainingTypes
     * @param $singleSessionTrainingTypes
     * @return array
     */
    public function getTrainingCrosstabs($multipleSessionTrainingTypes, $singleSessionTrainingTypes)
    {
        $return = array();

        $type = $this->index->getType('session');
        $filter = $this->getSessionFilter();
        $filter->addFilter(new BoolNot(new Terms('training.type', array_keys($singleSessionTrainingTypes))));

        $aggTypeEvt =  new \Elastica\Aggregation\Terms('training.typeLabel.source');
        $aggTypeEvt->setExclude('Rencontre scientifique');
        $aggTypeEvt->setMinimumDocumentCount(0);

        // ---
        // Thematiques
        // ---

        // theme / type de formation
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation(new TrainingThemeAggregation());
        $crosstab->setTerms('training.theme.source', $this->terms['theme']);
        $return['theme_type'] = $crosstab->execute(true);

        // Répartition de l'ensemble des formations par initiative
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation($aggTypeEvt);
        $crosstab->addAggregation("training.externalInitiative", array('true' => "Initiative interne", 'false' => 'A la demande'));
        $return['type_initiative'] = $crosstab->execute();

        // Répartition de l'ensemble des formations par type d'intervenant
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation($aggTypeEvt);
        $crosstab->addAggregation(new TrainerTypeAggregation());
        $return['type_intervenant'] = $crosstab->execute();

        // ---
        // Publics
        // ---
        $sum = new Sum('sum');
        $sum->setField('count');

        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation(new ParticipantsStatsAggregation());
        $crosstab->setTerms('publicType', $this->terms['public_type']);
        $return['public_type'] = $crosstab->execute(true);

        // ---
        // LEGACY
        // ---
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation($aggTypeEvt);

        $nested = new Nested('summary', 'participantsStats');
        $crosstab->addAggregation($nested, null, $sum);

        foreach((array_keys($multipleSessionTrainingTypes) + array('all')) as $key) {
            $_filter = clone $filter;
            if($key != "all") {
                $_filter->addFilter(new Term(array('training.type' => $key)));
            }
            $crosstab = new CrosstabReport($type, $_filter);

            $nested = new Nested('summary', 'participantsStats');
            $crosstab->addAggregation($nested, null, $sum);
        }

        // ---
        // ORIGINE GEOGRAPHIQUE
        // Le total ne peut pas être équivalent au sommaire car impossible de ventiler les participants d'une session aux inscriptions désactivées
        // ---
        // Répartition des publics par origine géographique
        foreach ((array_keys($multipleSessionTrainingTypes) + array('all')) as $key) {
            $_filter = clone $filter;
            $_filter->addFilter(new Term(array('training.type' => $key)));
            $crosstab = new CrosstabReport($type, $_filter);
            $crosstab->addAggregation(new PublicTypeGeographicOriginStatsAggregation());
            $crosstab->setTerms('geographicOrigin', $this->terms['geographic_origin']);
            $return['public_orig'][$key] = $crosstab->execute();
        }

        return $return;
    }

    /**
     * Get meeting crosses
     * @param $singleSessionTrainingTypes
     * @param $multipleSessionTrainingTypes
     * @return array
     */
    public function getSingleSessionTrainingCrosstabs($singleSessionTrainingTypes, $multipleSessionTrainingTypes)
    {
        $return = array();

        $type = $this->index->getType('session');
        $filter = $this->getSessionFilter();
        $filter->addFilter(new BoolNot(new Terms('training.type', array_keys($multipleSessionTrainingTypes))));

        // type (local)
        $_filter = clone $filter;
        $_filter->addFilter(new Term(array('training.national' => false)));
        $crosstab = new CrosstabReport($type, $_filter);
        $crosstab->addAggregation(new TrainingCategoryAggregation());
        $crosstab->setTerms('training.category.source', $this->terms['category']);
        $return['category']['local'] = $crosstab->execute();

        // type (national)
        $_filter = clone $filter;
        $_filter->addFilter(new Term(array('training.national' => true)));
        $crosstab = new CrosstabReport($type, $_filter);
        $crosstab->addAggregation(new TrainingCategoryAggregation());
        $crosstab->setTerms('training.category.source', $this->terms['category']);
        $return['category']['national'] = $crosstab->execute();

        // remove organization from filters
        $_query = $this->query->toArray();
        $filters = isset($_query['filter']['and']) ? $_query['filter']['and'] : array();
        foreach($filters as $key => $_filter) {
            if (isset($_filter['term']['training.organization.name.source'])) {
                unset($filters[$key]);
            }
        }

        //thématiques
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation(new TrainingThemeAggregation(true));
        $crosstab->setTerms('training.theme.source', $this->terms['theme']);
        $return['theme_single_session_training'] = $crosstab->execute(true);

        // public
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation(new ParticipantsStatsAggregation(true));
        $crosstab->setTerms('publicType', $this->terms['public_type']);
        $return['publicType_single_session_training'] = $crosstab->execute(true);

        //zone géographique
        $type = $this->index->getType('inscription');
        // catégories
        $filter = $this->getSessionFilter('session.id');
        $filter->addFilter(new Terms('session.training.type', array_keys($singleSessionTrainingTypes)));
        $filter->addFilter(new Term(array('presenceStatus.status' => PresenceStatus::STATUS_PRESENT)));
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation('session.training.type', $singleSessionTrainingTypes);
        $crosstab->addAggregation('zoneCompetence', array('Etablissement de rattachement', 'Agglomération', 'Zone de compétence', 'Hors zone'));
        $return['origingeo_single_session_training'] = $crosstab->execute(true);

        return $return;
    }
}
