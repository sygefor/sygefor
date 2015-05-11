<?php

namespace Sygefor\Bundle\ActivityReportBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Elastica\Aggregation\AbstractAggregation;
use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Sum;
use Elastica\Aggregation\ValueCount;
use Elastica\Filter\AbstractFilter;
use Elastica\Filter\Bool;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\BoolNot;
use Elastica\Filter\Exists;
use Elastica\Filter\Ids;
use Elastica\Filter\MatchAll;
use Elastica\Filter\Missing;
use Elastica\Filter\Regexp;
use Elastica\Filter\Term;
use Elastica\Filter\Terms;
use Elastica\Index;
use Elastica\Query;
use Elastica\Search;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Sygefor\Bundle\ActivityReportBundle\Report\CrosstabReport;
use Sygefor\Bundle\ActivityReportBundle\Report\CrosstabReport\CrosstabReportAggregation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $this->terms['public_type'] = $this->getSortedTerms('Sygefor\Bundle\CoreBundle\Entity\Term\PublicType', array('parent' => null));
        $this->terms['theme'] = $this->getSortedTerms('Sygefor\Bundle\TrainingBundle\Entity\Term\Theme');
        $this->terms['disciplinary'] = $this->getSortedTerms('Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary', array('parent' => null));
    }

    /**
     * Return a sorted list of terms
     */
    protected function getSortedTerms($class, array $criteria = array())
    {
        $repo = $this->em->getRepository($class);
        $terms = $repo->findBy($criteria, array('position' => 'ASC'));
        return array_map(function($type) { return $type->getName(); }, $terms);
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

        // specific : stages
        if($type == 'internship') {

            // Nombre d'heures / personnes
            $return['hours_participant'] = $sums['numberOfParticipants'] > 0 ? $sums['hourDuration'] / $sums['numberOfParticipants'] : 0;

            // Nombre de demande d'inscription
            $return['registrations'] = $sums['numberOfRegistrations'];

            // Recettes globales
            $return['taking'] = $sums['totalTaking'];
        }

        // specific : all trainings
        if(is_array($type)) {
            $query = $this->getAggregableQuery($sessionfilter);
            $agg = new \Elastica\Aggregation\Terms("training.externInitiative");
            $agg->setField("training.externInitiative");
            $query->addAggregation($agg);
            $rs = $this->index->getType('session')->search($query)->getAggregation("training.externInitiative");
            foreach($rs['buckets'] as $bucket) {
                $return['external_'.$bucket['key']] = $bucket['doc_count'];
            }
        }

        // specific : meeting
        if($type == 'meeting') {
            // Nombre de rencontre en partenariat
            $_filter = clone $sessionfilter;
            $_filter->addFilter(new Exists('training.partners'));
            $_query = new Query();
            $_query->setFilter($_filter);
            $return['sessions_partners'] = $this->index->getType('session')->count($_query);
        }

        return $return;
    }

    /**
     * Lists
     */
    public function getListing($types = array())
    {
        $search = $this->index->getType('session');
        $filter = $this->getSessionFilter();
        if(count($types)) {
            $filter->addFilter(new Terms('training.type', $types));
        }

        $query = new Query();
        $query->setFilter($filter);
        //$query->setSize(9999);

        $trainingKeys = array('id', 'name', 'theme', 'interventionType', 'publicTypes', 'teachingCursus', 'disciplinary', 'organism');
        $sumKeys = array('hourDuration', 'publicTypes', 'numberOfRegistrations', 'numberOfParticipants', 'totalCost');

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

            foreach($results as $result) {
                $session = $result->getData();

                // reference the training
                $type = $session['training']['type'];
                $id = $session['training']['id'];
                $training = &$return[$type][$id];

                if(!$training) {
                    $training = array_intersect_key($session['training'], array_flip($trainingKeys));
                    $training['sessions'] = 0;
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
                if(in_array($type, array('internship', 'training_course'))) {
                    if($session['trainers']) {
                        $training['isUrfist'] = $session['trainers'][0]['isUrfist'];
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
        foreach($types as $type) {
            $return[$type] = $this->getSummary($type);
        }
        if(count($types) > 1) {
            $return['all'] = $this->getSummary($types);
        }
        return $return;
    }

    /**
     * Get training crosses
     */
    public function getTrainingCrosstabs()
    {
        $return = array();

        $type = $this->index->getType('session');
        $filter = $this->getSessionFilter();
        $filter->addFilter(new BoolNot(new Term(array('training.type' => 'meeting'))));

        // ---
        // Thematiques
        // ---

        $aggTypeEvt =  new \Elastica\Aggregation\Terms('training.typeLabel.source');
        $aggTypeEvt->setExclude('Rencontre scientifique');
        $aggTypeEvt->setMinimumDocumentCount(0);

        // theme / type de formation
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation('training.theme.source', $this->terms['theme']);
        $crosstab->addAggregation($aggTypeEvt);
        $return['theme_type'] = $crosstab->execute();

        // ---
        // Types / Partenarias
        // ---

        // Répartition de l'ensemble des formations par type d'intervention (tp/pas tp)
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation($aggTypeEvt);
        $tp = $crosstab->addAggregation("training.tp", array('true' => "Avec TP", 'false' => 'Sans TP'));
        $return['type_intervention'] = $crosstab->execute();

        // Répartition de l'ensemble des formations par initiative
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation($aggTypeEvt);
        $crosstab->addAggregation("training.externInitiative", array('true' => "A l'inititive des URFIST", 'false' => 'A la demande'));
        $return['type_initiative'] = $crosstab->execute();

        // Répartition de l'ensemble des formations par type d'intervenant
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation($aggTypeEvt);
        $crosstab->addAggregation("trainers.isUrfist", array('true' => "Formateur URFIST", 'false' => 'Formateur extérieur'));
        $return['type_intervenant'] = $crosstab->execute();

        // ---
        // Publics
        // ---
        $sum = new Sum('sum');
        $sum->setField('count');

        // Répartition des publics dans les stages
        // Répartition des publics dans les enseignements de cursus
        // Répartition des publics dans les actions diverses
        // Répartition des publics dans l'ensemble des formations
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation($aggTypeEvt);

        $nested = new Nested('summary', 'participantsSummaries');
        $crosstab->addAggregation($nested, null, $sum);

        $types = array_combine($this->terms['public_type'], $this->terms['public_type']);
        $types['Professionnels de l’information'] = 'Autres professionnels de l’information';
        $crosstab->addAggregation('publicCategory', $types, $sum);
        $return['public_type'] = $crosstab->execute(true);

        // ---
        // LEGACY
        // ---
        $crosstab = new CrosstabReport($type, $filter);
        $crosstab->addAggregation($aggTypeEvt);

        $nested = new Nested('summary', 'participantsSummaries');
        $crosstab->addAggregation($nested, null, $sum);

        // filtered by "Professionnels de l’information"
        $filtered = new Filter('legacyPublicCategory');
        $filtered->setFilter(new Term(array('legacyPublicCategory' => 'Professionnels de l’information')));
        $crosstab->addAggregation($filtered, null, $sum);

        $crosstab->addAggregation('legacyPublicCategory', null, $sum);
        $return['legacy_public_type'] = $crosstab->execute(true);
        // ---


        // Répartition des étudiants dans les enseignements de cursus par discipline
        // Répartition des étudiants et enseignants-chercheurs dans les stages par discipline
        // Répartition des étudiants et enseignants-chercheurs dans les actions diverses par discipline
        // Répartition des étudiants et enseignants-chercheurs dans l'ensemble des formations par discipline
        foreach(array('internship', 'diverse_training', 'training_course', 'all') as $key) {
            $_filter = clone $filter;
            if($key != "all") {
                $_filter->addFilter(new Term(array('training.type' => $key)));
            }
            $crosstab = new CrosstabReport($type, $_filter);

            $nested = new Nested('summary', 'participantsSummaries');
            $crosstab->addAggregation($nested, null, $sum);

            // filtered by "Professionnels de l’information"
            $terms = array('Etudiant, stagiaire', 'Doctorant');
            if($key != 'training_course') {
                $terms[] = 'Enseignant du supérieur, chercheur';
            }
            $filtered = new Filter('publicCategory');
            $filtered->setFilter(new Terms('publicCategory', $terms));
            $crosstab->addAggregation($filtered, null, $sum);

            // public / discipline
            $crosstab->addAggregation('publicCategory', array_intersect_assoc($this->terms['public_type'], $terms), $sum);
            $crosstab->addAggregation('disciplinaryDomain', $this->terms['disciplinary'], $sum);

            $return['public_discipline'][$key] = $crosstab->execute();
        }

        // ---
        // ORIGINE GEOGRAPHIQUE
        // ---
        $type = $this->index->getType('inscription');
        $filter = $this->getSessionFilter('session.id');
        $filter->addFilter(new BoolNot(new Term(array('session.training.type' => 'meeting'))));

        // Répartition des publics par origine géographique
        foreach(array('internship', 'diverse_training', 'training_course') as $key) {
            $_filter = clone $filter;
            $_filter->addFilter(new Term(array('session.training.type' => $key)));
            $crosstab = new CrosstabReport($type, $_filter);
            $crosstab->addAggregation('publicCategory.source', $this->terms['public_type']);
            $crosstab->addAggregation('zoneCompetence', array('Etablissement de rattachement', 'Agglomération', 'Zone de compétence', 'Hors zone'));
            $return['public_orig'][$key] = $crosstab->execute();
        }

        return $return;
    }

    /**
     * Get meeting crosses
     */
    public function getMeetingCrosstabs()
    {
        $return = array();

        $type = $this->index->getType('session');
        $filter = $this->getSessionFilter();
        $filter->addFilter(new Term(array('training.type' => 'meeting')));

        // type / eventKind (local)
        $_filter = clone $filter;
        $_filter->addFilter(new Term(array('training.national' => false)));
        $crosstab = new CrosstabReport($type, $_filter);
        $crosstab->addAggregation('training.eventType.source');
        $crosstab->addAggregation('training.eventKind.source');
        $return['eventType_eventKind']['local'] = $crosstab->execute();


        // type / eventKind (national)

        // remove URFIST from filters
        $_query = $this->query->toArray();
        $filters = $_query['filter']['and'];
        foreach($filters as $key => $filter) {
            if(isset($filter['term']['training.organization.name.source'])) {
                unset($filters[$key]);
            }
        }

        $_filter = new BoolAnd();
        $_filter->setParam('and', array_values($filters));
        $_filter->addFilter(new Term(array('training.national' => true)));
        $crosstab = new CrosstabReport($type, $_filter);
        $crosstab->addAggregation('training.eventType.source');
        $crosstab->addAggregation('training.eventKind.source');
        $return['eventType_eventKind']['national'] = $crosstab->execute();

        return $return;
    }
}
