<?php

namespace ActivityReportBundle\Utils\Export;

use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class XlsExportPaginer.
 */
class XlsExportPaginer
{
    /**
     * @param $variables
     *
     * @return array
     */
    public function refactorDatas($variables)
    {
        // add data level in arrays to be compatible with excel writer
        $variables['report']['summaries'] = $this->transformArrays($variables['report']['summaries']);
        $variables['report']['crosstabs'] = $this->transformArrays($variables['report']['crosstabs']);
        $variables['report']['listings'] = $this->transformArrays($variables['report']['listings']);

        // align summaries
        $cpt = 0;
        $alignedWith = '';
        $alignedSummaries = array();
        foreach ($variables['report']['summaries'] as $key => $summary) {
            if (!($cpt++ % 2)) {
                $alignedWith = $key;
            } else {
                $summary['alignedWith'] = $alignedWith;
                $summary['espacedBy'] = 1;
            }
            $alignedSummaries[$key] = $summary;
        }

        $alignedSummaries = $this->roundFloatValues($this->transformSummaries($alignedSummaries));
        $crosstabs = $this->transformCrosstabs($variables['report']['crosstabs']);
        $listings = $this->transformListings($variables['report']['listings']);
        $crosstabs = $this->roundFloatValues($crosstabs);

        // return datas by page
        $arraysBySheet = array(
            array('title' => 'Synthèses', 'arrays' => $alignedSummaries),
            array('title' => 'Croisements', 'arrays' => $crosstabs),
            array('title' => 'Récaputalifs', 'arrays' => $listings),
        );

        return $arraysBySheet;
    }

    /**
     * @param $summaries
     *
     * @return array
     */
    protected function transformSummaries($summaries)
    {
        $summaries = array(
            'Bilans des stages' => $this->getInternshipArray($summaries['internship']),
            'Bilans des formations longues' => $this->getLongTrainingArray($summaries['long_training']),
            'Bilans des rencontres scientifiques' => $this->getMeetingArray($summaries['meeting']),
            'Bilans des formations' => $this->getAllSummaryArray($summaries['all']),
        );

        return $summaries;
    }

    /**
     * @param $crosstabs
     *
     * @return array
     */
    protected function transformCrosstabs($crosstabs)
    {
        // remove cols for 1way crosstabs
        foreach (['inscription_organization', 'inscription_hours', 'totalCost_type', 'totalTaking_type'] as $key) {
            unset($crosstabs[$key]['datas']['cols']);
        }

        $crosstabs = array(
            'Thèmes par types de formation' => $crosstabs['theme_type'],
            'Répartition des inscriptions par délégation' => $crosstabs['inscription_organization'],
            'Répartition des heures-personnes par délégation' => $crosstabs['inscription_hours'],
            'Répartition des coûts par type de formation' => $crosstabs['totalCost_type'],
            'Répartition des recettes par type de formation' => $crosstabs['totalTaking_type'],
        );

        return $crosstabs;
    }

    /**
     * @param $listings
     *
     * @return array
     */
    protected function transformListings($listings)
    {
        return  array(
            'Liste des stages' => isset($listings['internship']) ? $this->transformListingTraining($listings['internship']) : array(),
            'Liste des formations longues' => isset($listings['long_training']) ? $this->transformListingTraining($listings['long_training']) : array(),
            'Liste des rencontres scientifiques' => isset($listings['meeting']) ? $this->transformListingTraining($listings['meeting']) : array(),
        );
    }

    /**
     * @param $training
     *
     * @return array
     */
    protected function getInternshipArray($training)
    {
        return array(
            'datas' => array(
                'Nombre de stages' => $this->getArrayData($training['datas'], 'trainings'),
                'Nombre de sessions' => $this->getArrayData($training['datas'], 'sessions'),
                "Nombre d'heures de stages" => $this->getArrayData($training['datas'], 'hours'),
                'Nombre de personnes formées' => $this->getArrayData($training['datas'], 'participations'),
                "Nombre d'heures-personnes" => (float) number_format($this->getArrayData($training['datas'], 'hours_participant'), 2, '.', ''),
                'Coût global des stages' => (float) number_format($this->getArrayData($training['datas'], 'totalCost'), 2, '.', ''),
                'Coût moyen par stagiaire formé' => (float) number_format($this->getArrayData($training['datas'], 'participations'), 2, '.', ''),
                'Coût moyen des sessions' => (float) number_format($this->getArrayData($training['datas'], 'sessions'), 2, '.', ''),
                'Recettes globales des stages' => $this->getArrayData($training['datas'], 'taking'),
            ),
        );
    }

    /**
     * @param $training
     *
     * @return array
     */
    protected function getLongTrainingArray($training)
    {
        return array(
            'datas' => array(
                'Nombre de formations longues' => $this->getArrayData($training['datas'], 'trainings'),
                'Nombre de sessions' => $this->getArrayData($training['datas'], 'sessions'),
                "Nombre d'heures de formations longues" => $this->getArrayData($training['datas'], 'hours'),
                'Nombre de personnes formées' => $this->getArrayData($training['datas'], 'participations'),
                "Nombre d'heures-personnes" => (float) number_format($this->getArrayData($training['datas'], 'hours_participant'), 2, '.', ''),
                'Coût global des formations longues' => (float) number_format($this->getArrayData($training['datas'], 'totalCost'), 2, '.', ''),
                'Coût moyen par stagiaire formé' => (float) number_format($this->getArrayData($training['datas'], 'participations'), 2, '.', ''),
                'Coût moyen des sessions' => (float) number_format($this->getArrayData($training['datas'], 'sessions'), 2, '.', ''),
                'Recettes globales des formations longues' => $this->getArrayData($training['datas'], 'taking'),
            ),
        );
    }

    /**
     * @param $training
     *
     * @return array
     */
    protected function getMeetingArray($training)
    {
        return array(
            'datas' => array(
                'Nombre de rencontres scientifiques' => $this->getArrayData($training['datas'], 'trainings'),
                'Nombre de sessions' => $this->getArrayData($training['datas'], 'sessions'),
                "Nombre d'heures de rencontres scientifiques" => $this->getArrayData($training['datas'], 'hours'),
                'Nombre de personnes formées' => $this->getArrayData($training['datas'], 'participations'),
                "Nombre d'heures-personnes" => (float) number_format($this->getArrayData($training['datas'], 'hours_participant'), 2, '.', ''),
                'Coût global des rencontres scientifiques' => (float) number_format($this->getArrayData($training['datas'], 'totalCost'), 2, '.', ''),
                'Coût moyen par stagiaire formé' => (float) number_format($this->getArrayData($training['datas'], 'participations'), 2, '.', ''),
                'Coût moyen des sessions' => (float) number_format($this->getArrayData($training['datas'], 'sessions'), 2, '.', ''),
                'Recettes globales des rencontres scientifiques' => $this->getArrayData($training['datas'], 'taking'),
            ),
        );
    }

    /**
     * @param $training
     *
     * @return array
     */
    protected function getAllSummaryArray($training)
    {
        return array(
            'datas' => array(
                'Nombre de formations' => $this->getArrayData($training['datas'], 'trainings'),
                'Nombre de sessions' => $this->getArrayData($training['datas'], 'sessions'),
                "Nombre d'heures de formation" => $this->getArrayData($training['datas'], 'hours'),
                'Nombre de personnes formées' => $this->getArrayData($training['datas'], 'participations'),
                "Nombre d'heures-personnes" => (float) number_format($this->getArrayData($training['datas'], 'hours_participant'), 2, '.', ''),
                'Coût global des formations' => (float) number_format($this->getArrayData($training['datas'], 'totalCost'), 2, '.', ''),
                'Coût moyen par stagiaire formé' => (float) number_format($this->getArrayData($training['datas'], 'participations'), 2, '.', ''),
                'Coût moyen des sessions' => (float) number_format($this->getArrayData($training['datas'], 'sessions'), 2, '.', ''),
                'Recettes globales des formations' => $this->getArrayData($training['datas'], 'taking'),
            ),
            'espacedBy' => 1,
        );
    }

    /**
     * @param $listing
     *
     * @return array
     */
    protected function transformListingTraining($listing)
    {
        $arrayKeys = array('name', 'theme', 'sessions', 'hourNumber', 'numberOfRegistrations', 'numberOfParticipants', 'totalCost', 'totalTaking');
        $transformedTrainings = array();
        foreach ($listing['datas'] as $training) {
            foreach ($arrayKeys as $key) {
                if (!isset($training[$key])) {
                    $training[$key] = '';
                }
            }

            $transformedTrainings[] = array(
                'Intitulé' => $training['name'],
                'Thème' => $training['theme']['name'],
                'Nombre de sessions' => $training['sessions'],
                'Nombre d\'heures' => $training['hourNumber'],
                'Demandes d\'inscriptions' => $training['numberOfRegistrations'],
                'Participants' => $training['numberOfParticipants'],
                'Coût' => $training['totalCost'],
                'Recettes' => $training['totalTaking'],
            );
        }

        return array('datas' => $transformedTrainings);
    }

    /**
     * @param $array
     * @param $key
     *
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
     *
     * @return array
     */
    protected function transformArrays($array, $keyOrder = null)
    {
        $transformedArray = array();
        foreach ($array as $key => $values) {
            $transformedArray[$key] = array(
                'datas' => $values,
            );
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

    /**
     * @param array $values
     *
     * @return array
     */
    protected function roundFloatValues($values)
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->roundFloatValues($value);
            }
            if (is_float($values) || is_double($value)) {
                $values[$key] = round($value, 2);
            }
        }

        return $values;
    }

    /**
     * @param $crosstabs
     * @param $key
     * @param $labels
     */
    protected function overrideLabels(&$crosstabs, $key, $labels)
    {
        foreach (['cols', 'rows'] as $column) {
            if (isset($crosstabs[$key]['datas'][$column])) {
                foreach ($crosstabs[$key]['datas'][$column] as $keyLabel => $label) {
                    if (in_array($label['label'], array_keys($labels))) {
                        $crosstabs[$key]['datas'][$column][$keyLabel]['label'] = $labels[$crosstabs[$key]['datas'][$column][$keyLabel]['label']];
                        $crosstabs[$key]['datas'][$column][$keyLabel]['key'] = $labels[$crosstabs[$key]['datas'][$column][$keyLabel]['key']];
                    }
                }
            }
        }
    }
}
