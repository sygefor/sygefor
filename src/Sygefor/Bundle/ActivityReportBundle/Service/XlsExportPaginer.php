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
        $variables['report']['summaries'] = $this->transformArrays($variables['report']['summaries'], ['internship', 'doctoral_training', 'training_course', 'diverse_training', 'all', 'meeting']);
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
            'Actions diverses' => $this->getDiverseTrainingArray($summaries['diverse_training']),
            'Total des activités de formation' => $this->getAllSummaryArray($summaries['all']),
            // empty, just display title
            'Enseignements de cursus' => array(
                'datas' => array(),
                'alignedWith' => 'Stages',
                'espacedBy' => 1
            ),
            'Formations doctorales' => $this->getDoctoralTrainingArray($summaries['doctoral_training']),
            'Autres enseignements de cursus' => $this->getTrainingCourseArray($summaries['training_course']),
            'Bilan global des enseignements de cursus' => $this->getAllTrainingCourseSummaryArray($summaries['training_course'], $summaries['doctoral_training']),
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
                'Nombre de rencontres locales par type et par nature' => $crossMeetings['datas']['local'],
                'Nombre de rencontres nationales par type et par nature' => $crossMeetings['datas']['national']
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
            'Liste des formations doctorales' => isset($listings['doctoral_training']) ? $this->transformListingDoctoralTraining($listings['doctoral_training']) : array(),
            'Liste des enseignements de cursus' => isset($listings['training_course']) ? $this->transformListingTrainingCourse($listings['training_course']) : array(),
            'Liste des actions diverses' => isset($listings['diverse_training']) ? $this->transformListingDiverseTraining($listings['diverse_training']) : array(),
            'Liste des rencontres scientifiques' => isset($listings['meeting']) ? $this->transformListingMeeting($listings['meeting']) : array()
        ];
    }

    /**
     * @param $meetingSummary
     * @param $crossMeetings
     * @return array
     */
    protected function transformMeetings($meetingSummary, $crosstabs)
    {
        return [
            'Synthèse' => $meetingSummary,
            'Croisements' => $this->transformCrossMeetings($crosstabs['eventType_eventKind']),
            'Nombre de rencontres par thématiques' => $crosstabs['theme_meeting'],
            'Nombre d\'inscriptions par type de public' => $crosstabs['publicType_meeting'],
            'Nombre d\'inscriptions par origine géographique' => $crosstabs['origingeo_meeting'],
            'Publics par discipline' => $crosstabs['meeting_public_discipline']
        ];
    }

    /**
     * @param $listing
     * @return array
     */
    protected function transformListingInternship($listing)
    {
        $arrayKeys = ['name', 'sessions', 'hourDuration', 'isUrfist', 'theme', 'interventionType', 'publicTypes', 'numberOfRegistrations', 'numberOfParticipants', 'totalCost'];
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
                'Type d\'intervenant' => $internship['isUrfist'] ? 'URFIST' : 'Extérieur',
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
    protected function transformListingDoctoralTraining($listing)
    {
        $arrayKeys = ['name', 'theme', 'sessions', 'hourDuration', 'isUrfist', 'numberOfParticipants', 'pedagogicPartner', 'otherPartner', 'totalCost'];
        $transformedTrainingCourse = array();
        foreach ($listing['datas'] as $trainingCourse) {
            foreach ($arrayKeys as $key) {
                if (!isset($trainingCourse[$key])) {
                    $trainingCourse[$key] = "";
                }
            }
            $transformedTrainingCourse[] = [
                'Intitulé' => $trainingCourse['name'],
                'Thématique' => $trainingCourse['theme'],
                'Nombre de cours' => $trainingCourse['sessions'],
                'Nombre d\'heures' => $trainingCourse['hourDuration'],
                'Type d\'intervenant' => $trainingCourse['isUrfist'] ? 'URFIST' : 'Extérieur',
                'Participants' => $trainingCourse['numberOfParticipants'],
                'Partenaire pédagogique' => $trainingCourse['pedagogicPartner'],
                'Autre partenaire' => $trainingCourse['otherPartner'],
                'Coût' => $trainingCourse['totalCost'],
            ];
        }

        return ['datas' => $transformedTrainingCourse];
    }

    /**
     * @param $listing
     * @return array
     */
    protected function transformListingTrainingCourse($listing)
    {
        $arrayKeys = ['name', 'theme', 'sessions', 'hourDuration', 'teachingCursus', 'numberOfParticipants', 'disciplinary', 'interventionType', 'totalCost'];
        $transformedTrainingCourse = array();
        foreach ($listing['datas'] as $trainingCourse) {
            foreach ($arrayKeys as $key) {
                if (!isset($trainingCourse[$key])) {
                    $trainingCourse[$key] = "";
                }
            }
            $transformedTrainingCourse[] = [
                'Intitulé' => $trainingCourse['name'],
                'Thématique' => $trainingCourse['theme'],
                'Nombre de cours' => $trainingCourse['sessions'],
                'Nombre d\'heures' => $trainingCourse['hourDuration'],
                'Cursus' => $trainingCourse['teachingCursus'],
                'Participants' => $trainingCourse['numberOfParticipants'],
                'Discipline' => $trainingCourse['disciplinary'],
                'Type de formation' => $trainingCourse['interventionType'],
                'Coût' => $trainingCourse['totalCost'],
            ];
        }

        return ['datas' => $transformedTrainingCourse];
    }

    /**
     * @param $listing
     * @return array
     */
    protected function transformListingDiverseTraining($listing)
    {
        $arrayKeys = ['name', 'theme', 'sessions', 'hourDuration', 'publicTypes', 'numberOfParticipants', 'interventionType', 'organism', 'totalCost'];
        $transformedDiverseTraining = array();
        foreach ($listing['datas'] as $diverseTraining) {
            foreach ($arrayKeys as $key) {
                if (!isset($diverseTraining[$key])) {
                    $diverseTraining[$key] = "";
                }
            }
            $transformedDiverseTraining[] = [
                'Intitulé' => $diverseTraining['name'],
                'Thématique' => $diverseTraining['theme'],
                'Nombre d\'actions' => $diverseTraining['sessions'],
                'Nombre d\'heures' => $diverseTraining['hourDuration'],
                'Types de public' => $diverseTraining['publicTypes'],
                'Participants' => $diverseTraining['numberOfParticipants'],
                'Type de formation' => $diverseTraining['interventionType'],
                'Partenaire' => $diverseTraining['organism'],
                'Coût' => $diverseTraining['totalCost'],
            ];
        }

        return ['datas' => $transformedDiverseTraining];
    }

    /**
     * @param $listing
     * @return array
     */
    protected function transformListingMeeting($listing)
    {
        $arrayKeys = ['name', 'session.date', 'eventKind', 'eventType', 'national', 'partners', 'numberOfRegistrations', 'numberOfParticipants', 'totalCost', 'totalTaking'];
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
                'Nature' => $meeting['eventKind'],
                'Type' => $meeting['eventType'],
                'National' => $meeting['national'] ? 'Oui' : 'Non',
                'Thématique' => $meeting['theme'],
                'Partenaires' => $meeting['partners'],
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
            'Professionnels de l\'information / formation' => $crosstabs['legacy_public_type'],
            'Publics / origines géographiques' => $this->transformPublicOrgin($crosstabs['public_orig']),
            'Publics / discipline' => $this->transformPublicDiscipline($crosstabs['public_discipline']),
            'Répartition de l\'ensemble des formations par type d\'intervention' => $crosstabs['type_intervention'],
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
                'Répartition des étudiants dans les enseignements de cursus par discipline' => $publicDiscipline['datas']['doctoral_training'],
                'Répartition des étudiants dans les formations doctorales par discipline' => $publicDiscipline['datas']['training_course'],
                'Répartition des étudiants et enseignants-chercheurs dans les stages par discipline' => $publicDiscipline['datas']['internship'],
                'Répartition des étudiants et enseignants-chercheurs dans les actions diverses par discipline' => $publicDiscipline['datas']['diverse_training'],
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
                'Répartition des publics dans les formations doctorales par origine géographique' => $publicOrigins['datas']['doctoral_training'],
                'Répartition des publics dans les enseignements de cursus par origine géographique' => $publicOrigins['datas']['diverse_training'],
                'Répartition des publics dans les actions diverses par origine géographique' => $publicOrigins['datas']['training_course'],
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
    protected function getDoctoralTrainingArray($doctoralTraining)
    {
        return [
            'datas' => [
                "Nombre d'interventions" => $this->getArrayData($doctoralTraining['datas'], 'trainings'),
                "Nombre d'heures de formation" => $this->getArrayData($doctoralTraining['datas'], 'hours'),
                "Nombre de personnes formées" => $this->getArrayData($doctoralTraining['datas'], 'participations')
            ],
            'alignedWith' => 'Enseignements de cursus',
            'espacedBy' => "1:2"
        ];
    }

    /**
     * @param $trainingCourse
     * @return array
     */
    protected function getTrainingCourseArray($trainingCourse)
    {
        return [
            'datas' => [
                "Nombre d'interventions" => $this->getArrayData($trainingCourse['datas'], 'trainings'),
                "Nombre d'heures de formation" => $this->getArrayData($trainingCourse['datas'], 'hours'),
                "Nombre de personnes formées" => $this->getArrayData($trainingCourse['datas'], 'participations')
            ],
            'alignedWith' => 'Enseignements de cursus',
            'espacedBy' => "1:7"
        ];
    }

    /**
     * @param $trainingCourse
     * @return array
     */
    protected function getAllTrainingCourseSummaryArray($trainingCourse, $doctoralTraining)
    {
        return [
            'datas' => [
                "Nombre total d'interventions" => $this->getArrayData($trainingCourse['datas'], 'trainings', $doctoralTraining['datas'], '+'),
                "Nombre total d'heures de formation" => $this->getArrayData($trainingCourse['datas'], 'hours', $doctoralTraining['datas'], '+'),
                "Nombre total de personnes formées" => $this->getArrayData($trainingCourse['datas'], 'participations', $doctoralTraining['datas'], '+'),
                "Coût global" => (float)number_format($this->getArrayData($trainingCourse['datas'], 'cost', $doctoralTraining['datas'], '+'), 2, '.', ''),
                "Recettes globales" => $this->getArrayData($trainingCourse['datas'], 'taking', $doctoralTraining['datas'], '+')
            ],
            'alignedWith' => 'Enseignements de cursus',
            'espacedBy' => "1:12"
        ];
    }

    /**
     * @param $diverseTraining
     * @return array
     */
    protected function getDiverseTrainingArray($diverseTraining)
    {
        return [
            'datas' => [
                "Nombre d'actions diverses" => $this->getArrayData($diverseTraining['datas'], 'trainings'),
                "Nombre d'heures de formation" => $this->getArrayData($diverseTraining['datas'], 'hours'),
                "Nombre de personnes formées" => $this->getArrayData($diverseTraining['datas'], 'participations'),
                "Coût global" => (float)number_format($this->getArrayData($diverseTraining['datas'], 'cost'), 2, '.', ''),
                "Recettes globales" => $this->getArrayData($diverseTraining['datas'], 'taking')
            ]
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
                "Nombre de rencontres scientifiques et professionnelles" => $this->getArrayData($meeting['datas'], 'trainings'),
                "Nombre de participants" => $this->getArrayData($meeting['datas'], 'participations'),
                "Nombre de rencontres en partenariat" => $this->getArrayData($meeting['datas'], 'sessions_partners')
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
                "Nombre de formations à l'initiative de l'URFIST" => $this->getArrayData($all['datas'], 'external_false'),
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