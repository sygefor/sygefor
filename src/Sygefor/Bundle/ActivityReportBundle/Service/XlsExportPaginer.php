<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 28/08/2015
 * Time: 10:45
 */

namespace Sygefor\Bundle\ActivityReportBundle\Service;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;


/**
 * Class XlsExportPaginer
 * @package Sygefor\Bundle\ActivityReportBundle\Service
 */
class XlsExportPaginer
{
    /**
     * @param $variables
     * @return array
     */
    public function refactorDatas($variables)
    {
        // add data level in arrays to be compatible with excel writer
        $variables['report']['summaries'] = $this->transformArrays($variables['report']['summaries'], ['internship', 'long_training', 'all', 'meeting']);
        $variables['report']['crosstabs'] = $this->transformArrays($variables['report']['crosstabs']);
        $variables['report']['listings'] = $this->transformArrays($variables['report']['listings']);

        // align summaries
        $cpt = 0;
        $alignedWith = "";
        $alignedSummaries = array();
        foreach ($variables['report']['summaries'] as $key => $summary) {
            if (!($cpt++ % 2)) {
                $alignedWith = $key;
            }
            else {
                $summary['alignedWith'] = $alignedWith;
                $summary['espacedBy'] = 1;
            }
            $alignedSummaries[$key] = $summary;
        }

        // refactor summaries
        $alignedSummaries = $this->transformSummaries($alignedSummaries);
        // refactor crosstabs
        $crosstabs = $this->transformCrosstabs($variables['report']['crosstabs']);
        // refactor listings
        $listings = $this->transformListings($variables['report']['listings']);
        // refactor meetings
        $meetings = $this->transformMeetings($alignedSummaries['Rencontres scientifiques'], $variables['report']['crosstabs']);
        // remove meeting from page 1
        unset($alignedSummaries['Rencontres scientifiques']);

        // return datas by page
        $arraysBySheet = array(
            ['title' => 'Synthèses', 'arrays' => $alignedSummaries],
            ['title' => 'Croisements', 'arrays' => $crosstabs],
            ['title' => 'Rencontres scientifiques', 'arrays' => $meetings],
            ['title' => 'Récaputalifs', 'arrays' => $listings],
        );

        return $arraysBySheet;
    }

    /**
     * @param $summaries
     * @return array
     */
    protected function transformSummaries($summaries)
    {
        $summaries = array(
            'Stages' => $this->getInternshipArray($summaries['internship']),
            'Formation longues' => $this->getLongTrainingArray($summaries['long_training']),
            'Total des activités de formation' => $this->getAllSummaryArray($summaries['all']),
            'Rencontres scientifiques' => $this->getMeetingArray($summaries['meeting'])
        );
        return $summaries;
    }

    /**
     * @param $crossMeetings
     * @return array
     */
    protected function transformCrossMeetings($crossMeetings)
    {
        return [
            'datas' => [
                'Nombre de rencontres locales par type' => $crossMeetings['datas']['local'],
                'Nombre de rencontres nationales par type' => $crossMeetings['datas']['national']
            ]
        ];
    }

    /**
     * @param $listings
     * @return array
     */
    protected function transformListings($listings)
    {
        return  [
            'Liste des stages' => isset($listings['internship']) ? $this->transformListingInternship($listings['internship']) : array(),
            'Liste des formations longues' => isset($listings['long_training']) ? $this->getLongTrainingArray($listings['long_training']) : array(),
            'Liste des rencontres scientifiques' => isset($listings['meeting']) ? $this->transformListingMeeting($listings['meeting']) : array()
        ];
    }

    /**
     * @param $meetingSummary
     * @param $crosstabs
     * @return array
     */
    protected function transformMeetings($meetingSummary, $crosstabs)
    {
        return [
            'Synthèse' => $meetingSummary,
            'Croisements' => $this->transformCrossMeetings($crosstabs['category']),
            'Nombre de rencontres par thématiques' => $crosstabs['theme_single_session_training'],
            'Nombre d\'inscriptions par type de public' => $crosstabs['publicType_single_session_training'],
            'Nombre d\'inscriptions par origine géographique' => $crosstabs['origingeo_single_session_training']
        ];
    }

    /**
     * @param $listing
     * @return array
     */
    protected function transformListingInternship($listing)
    {
        $arrayKeys = ['name', 'sessions', 'hourDuration', 'isOrganization', 'theme', 'interventionType', 'publicTypes', 'numberOfRegistrations', 'numberOfParticipants', 'totalCost'];
        $transformedInternships = array();
        foreach ($listing['datas'] as $internship) {
            foreach ($arrayKeys as $key) {
                if (!isset($internship[$key])) {
                    $internship[$key] = "";
                }
            }

            $transformedInternships[] = [
                'Intitulé' => $internship['name'],
                'Nombre de sessions' => $internship['sessions'],
                'Nombre d\'heures' => $internship['hourDuration'],
                'Type d\'intervenant' => $internship['isOrganization'] ? 'Interne' : 'Extérieur',
                'Thématique' => $internship['theme'],
                'Type de formation' => $internship['interventionType'],
                'Types de public' => $internship['publicTypes'],
                'Demandes d\'inscriptions' => $internship['numberOfRegistrations'],
                'Participants' => $internship['numberOfParticipants'],
                'Coût' => $internship['totalCost'],
            ];
        }
        return ['datas' => $transformedInternships];
    }

    /**
     * @param $listing
     * @return array
     */
    protected function transformListingLongTraining($listing)
    {
        $arrayKeys = ['name', 'sessions', 'hourDuration', 'isOrganization', 'theme', 'interventionType', 'publicTypes', 'numberOfRegistrations', 'numberOfParticipants', 'totalCost'];;
        $transformedLongTraining = array();
        foreach ($listing['datas'] as $longTraining) {
            foreach ($arrayKeys as $key) {
                if (!isset($longTraining[$key])) {
                    $longTraining[$key] = "";
                }
            }
            $transformedLongTraining[] = [
                'Intitulé' => $transformedLongTraining['name'],
                'Nombre de sessions' => $transformedLongTraining['sessions'],
                'Nombre d\'heures' => $transformedLongTraining['hourDuration'],
                'Type d\'intervenant' => $transformedLongTraining['isOrganization'] ? 'Interne' : 'Extérieur',
                'Thématique' => $transformedLongTraining['theme'],
                'Type de formation' => $transformedLongTraining['interventionType'],
                'Types de public' => $transformedLongTraining['publicTypes'],
                'Demandes d\'inscriptions' => $transformedLongTraining['numberOfRegistrations'],
                'Participants' => $transformedLongTraining['numberOfParticipants'],
                'Coût' => $transformedLongTraining['totalCost'],
            ];
        }

        return ['datas' => $transformedLongTraining];
    }

    /**
     * @param $listing
     * @return array
     */
    protected function transformListingMeeting($listing)
    {
        $arrayKeys = ['name', 'session.date', 'category', 'national', 'numberOfRegistrations', 'numberOfParticipants', 'totalCost', 'totalTaking'];
        $transformedMeeting = array();
        foreach ($listing['datas'] as $meeting) {
            foreach ($arrayKeys as $key) {
                if (!isset($meeting[$key])) {
                    $meeting[$key] = "";
                }
            }

            $transformedMeeting[] = [
                'Titre' => $meeting['name'],
                'Date' => (new \DateTime($meeting['session.date']))->format('d/m/Y'),
                'Type' => $meeting['category'],
                'National' => $meeting['national'] ? 'Oui' : 'Non',
                'Thématique' => $meeting['theme'],
                'Demandes d\'inscriptions' => $meeting['numberOfRegistrations'],
                'Participants' => $meeting['numberOfParticipants'],
                'Coût' => $meeting['totalCost'],
                'Recette' => $meeting['totalTaking']
            ];
        }

        return ['datas' => $transformedMeeting];
    }

    /**
     * @param $crosstabs
     * @return array
     */
    protected function transformCrosstabs($crosstabs)
    {
        $crosstabs = array(
            'Thématiques' => $crosstabs['theme_type'],
            'Publics / formation' => $crosstabs['public_type'],
            'Publics / origines géographiques' => $this->transformPublicOrgin($crosstabs['public_orig']),
            'Répartition de l\'ensemble des formations des formations par initiative' => $crosstabs['type_initiative'],
            'Répartition de l\'ensemble des formations par type d\'intervenant' => $crosstabs['type_intervenant'],
        );
        return $crosstabs;
    }

    /**
     * @param $publicDiscipline
     * @return array
     */
    protected function transformPublicDiscipline($publicDiscipline)
    {
        return [
            'datas' => [
                'Répartition des étudiants et enseignants-chercheurs dans les stages par discipline' => $publicDiscipline['datas']['internship'],
                'Répartition des étudiants dans les formations longues par discipline' => $publicDiscipline['datas']['long_training'],
                'Répartition des étudiants et enseignants-chercheurs dans l\'ensemble des formations par discipline' => $publicDiscipline['datas']['all'],
            ]
        ];
    }

    /**
     * @param $publicOrigins
     * @return array
     */
    protected function transformPublicOrgin($publicOrigins)
    {
        return [
            'datas' => [
                'Répartition des publics dans les stages par origine géographique' => $publicOrigins['datas']['internship'],
                'Répartition des publics dans les formations longues par origine géographique' => $publicOrigins['datas']['long_training'],
            ]
        ];
    }

    /**
     * @param $internship
     * @return array
     */
    protected function getInternshipArray($internship)
    {
        return [
            'datas' => [
                "Nombre de stages" => $this->getArrayData($internship['datas'], 'trainings'),
                "Nombre de sessions" => $this->getArrayData($internship['datas'], 'sessions'),
                "Nombre d'heures de formation" => $this->getArrayData($internship['datas'], 'hours'),
                "Nombre d'heures-personnes" => (float)number_format($this->getArrayData($internship['datas'], 'hours_participant'), 2, '.', ''),
                "Nombre de demandes d'inscriptions" => $this->getArrayData($internship['datas'], 'registrations'),
                "Nombre de personnes formées" => $this->getArrayData($internship['datas'], 'participations'),
                "Coût global" => (float)number_format($this->getArrayData($internship['datas'], 'cost'), 2, '.', ''),
                "Recettes globales" => $this->getArrayData($internship['datas'], 'taking')
            ]
        ];
    }

    /**
     * @param $trainingCourse
     * @return array
     */
    protected function getLongTrainingArray($doctoralTraining)
    {
        return [
            'datas' => [
                "Nombre d'interventions" => $this->getArrayData($doctoralTraining['datas'], 'trainings'),
                "Nombre d'heures de formation" => $this->getArrayData($doctoralTraining['datas'], 'hours'),
                "Nombre de personnes formées" => $this->getArrayData($doctoralTraining['datas'], 'participations')
            ],
            'espacedBy' => "1:2"
        ];
    }

    /**
     * @param $meeting
     * @return array
     */
    protected function getMeetingArray($meeting)
    {
        return [
            'datas' => [
                "Nombre de rencontres scientifiques" => $this->getArrayData($meeting['datas'], 'trainings'),
                "Nombre de participants" => $this->getArrayData($meeting['datas'], 'participations')
            ]
        ];
    }

    /**
     * @param $all
     * @return array
     */
    protected function getAllSummaryArray($all)
    {
        return [
            'datas' => [
                "Nombre d'actions de formations" => $this->getArrayData($all['datas'], 'trainings'),
                "Nombre de sessions" => $this->getArrayData($all['datas'], 'sessions'),
                "Nombre d'heures de formation" => $this->getArrayData($all['datas'], 'hours'),
                "Nombre de personnes formées" => $this->getArrayData($all['datas'], 'participations'),
                "Nombre de formations à d'initiative interne" => $this->getArrayData($all['datas'], 'external_false'),
                "Nombre de formations à la demande d'extérieurs" => $this->getArrayData($all['datas'], 'external_true'),
                "Coût global" => (float)number_format($this->getArrayData($all['datas'], 'cost'), 2, '.', ''),
                "Recettes globales" => $this->getArrayData($all['datas'], 'taking')
            ],
            'espacedBy' => 1
        ];
    }

    /**
     * @param $array
     * @param $key
     * @return string
     */
    protected function getArrayData($array, $key, $array2 = null, $op = null)
    {
        $value = null;
        if (isset($array[$key])) {
            $value = $array[$key];
        }
        if (isset($array2) && isset($array2[$key])) {
            if ($op === null) {
                throw new UnexpectedTypeException('Op variable cannot be null');
            }
            if ($op === '+') {
                $value += $array2[$key];
            }
        }

        return $value;
    }

    /**
     * @param $array
     * @param null $keyOrder
     * @return array
     */
    protected function transformArrays($array, $keyOrder = null)
    {
        $transformedArray = array();
        foreach ($array as $key => $values) {
            $transformedArray[$key] = [
                'datas' => $values
            ];
        }

        $orderedArray = array();
        if ($keyOrder) {
            foreach ($keyOrder as $key) {
                if (isset($transformedArray[$key])) {
                    $orderedArray[$key] = $transformedArray[$key];
                }
            }
            $transformedArray = $orderedArray;
        }

        return $transformedArray;
    }
}