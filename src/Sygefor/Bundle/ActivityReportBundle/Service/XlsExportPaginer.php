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
        $variables['report']['summaries'] = $this->transformArrays($variables['report']['summaries'], ['all']);
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

        // return datas by page
        $arraysBySheet = array(
            ['title' => 'Synthèses', 'arrays' => $alignedSummaries],
            ['title' => 'Croisements', 'arrays' => $crosstabs],
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
            'Total des activités de formation' => $this->getAllSummaryArray($summaries['all'])
        );
        return $summaries;
    }

    /**
     * @param $listings
     * @return array
     */
    protected function transformListings($listings)
    {
        return  [
            'Liste des stages' => isset($listings['internship']) ? $this->transformListingInternship($listings['internship']) : array()
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