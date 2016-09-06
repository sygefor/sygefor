<?php

namespace Sygefor\Bundle\ActivityReportBundle\Service;

class ExcelWriter
{
    /** @var \PHPExcel */
    protected $ea;

    /** @var array */
    protected $positionInSheets;

    /** @var array */
    protected $nbRowBySheet;

    /** @var array */
    protected $formatExtentions;

    /** @var array */
    protected $arrayPositionPositionBeginingBySheet;

    /** @var array */
    protected $translationsBySheet;

    function __construct()
    {
        $this->formatExtentions = array(
          'Excel2007' => 'xlsx'
        );
    }

    /**
     * Create and save xls file
     * @param $variables
     * @param $name
     * @param null $creator
     * @param null $description
     * @param null $tags
     */
    public function getXls($variables, $name, $filePath = "", $creator = null, $description = null, $tags = null)
    {
        $this->createXls($name, $creator, $description, $tags);
        $this->setArraysToSheets($variables);
        $filePath = $this->saveFile($name, $filePath);
        return $filePath;
    }

    /**
     * @param $arraysBySheet
     */
    public function setArraysToSheets($arraysBySheet)
    {
        foreach ($arraysBySheet as $numberSheet => $sheet) {
            foreach ($sheet as $title => $value) {
                if ($title === 'title') {
                    $this->setTitleToSheet($value, $numberSheet);
                }
                if ($title === 'arrays') {
                    $this->setArraysToSheet($value, $numberSheet);
                }
                $this->autosizeColumns($numberSheet);
            }
        }
    }

    /**
     * @param $title
     * @param int $sheet
     */
    public function setTitleToSheet($title, $sheet = 0)
    {
        $ews = $this->getSheet($sheet);
        $ews->setTitle($title);
    }

    /**
     * @param $values
     * @param $sheet
     */
    public function setArraysToSheet($values, $sheet)
    {
        foreach ($values as $arrayName => $datas) {
            $this->setDatasArray($sheet, $arrayName, $datas);
        }
    }

    /**
     * @param $sheet
     * @param $datas
     */
    public function setDatasArray($sheet, $arrayName, $datas)
    {
        // get positions for the new array
        $positions = $this->getArrayBeginingCoordinates($sheet, $arrayName, $datas);
        $columnBegining = $positions['column'];
        $columnEnd = $columnBegining;
        $ligneBegining = $positions['ligne'];

        // set array title
        $this->setHeaderArray($sheet, $arrayName, $columnBegining, $ligneBegining);
        $savedPositionInSheet = $this->positionInSheets[$sheet];
        $this->positionInSheets[$sheet] = $ligneBegining + 1;

        $setEntityTitlesForEntityTables = true;
        $arrayType = -1;
        if (!isset($datas['datas'])) {
            return;
        }
        foreach ($datas['datas'] as $key => $value) {
            // if value is an array
            if (is_array($value)) {
                if (!$this->is_empty($value)) {
                    // cross datas
                    if (isset($value['cols'])) {
                        $this->setCrossTable($key, $value, $sheet, $columnBegining);
                        $arrayType = 0;
                    }
                    // aggregated datas
                     else if ($this->isAggregatedTable($value)) {
                        $columnEnd = $this->setAggregatedTable($value, $sheet, $columnBegining);
                        $arrayType = 1;
                    }
                    // serialized entities
                    else {
                        $columnEnd = $this->setEntityTable($value, $sheet, $setEntityTitlesForEntityTables, $columnBegining);
                        $setEntityTitlesForEntityTables = false;
                        $arrayType = 2;
                    }
                }
            }
            // just a key value input
            else if ($key !== "value") {
                $columnEnd = $this->setSummaryTable($sheet, $key, $value, $columnBegining);
                $arrayType = 3;
            }
        }
        $ligneEnd = $this->positionInSheets[$sheet];

        // set style to arrays
        if ($arrayType != 0) {
            $this->setArrayStyle($arrayType, $columnBegining, $columnEnd, $ligneBegining + 1, $ligneEnd - 1, $sheet);
        }

        // save array position to align others
        $this->saveArrayPositionBegining($sheet, $arrayName, $columnEnd, $ligneBegining);

        // keep position in sheet
        $this->positionInSheets[$sheet] = ($this->positionInSheets[$sheet] > $savedPositionInSheet ? $this->positionInSheets[$sheet] : $savedPositionInSheet) + 1;
    }

    /**
     * @param $array
     * @return bool
     */
    protected function isAggregatedTable($array)
    {
        foreach ($array as $value) {
            if (isset($value['key']) && isset($value['value']) && isset($value['label'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $sheet
     * @param $arrayName
     * @param $datas
     * @return array
     */
    protected function getArrayBeginingCoordinates($sheet, $arrayName, $datas)
    {
        $alignedWith = "";
        if (isset($datas['alignedWith'])) {
            $alignedWith = $datas['alignedWith'];
        }
        $espacedBy = "";
        $espacedByLine = 0;
        if (isset($datas['espacedBy'])) {
            if (is_string($datas['espacedBy'])) {
                $parts = explode(':', $datas['espacedBy']);
                if (count($parts) > 0) {
                    $espacedBy = intval($parts[0]);
                    $espacedByLine = intval($parts[1]);
                }
            }
            else {
                $espacedBy = $datas['espacedBy'];
            }
        }

        $columnBegining = "A";
        $ligneBegining = $this->positionInSheets[$sheet];
        if (!empty($alignedWith) && !empty($espacedBy)) {
            $positions = $this->getArrayPositionBegining($sheet, $alignedWith);
            $offset = $espacedByLine === 0 ? 1 : -1;
            $columnBegining = chr($positions['column'] + $espacedBy + $offset);
            $ligneBegining = $positions['ligne'] + $espacedByLine;
        }

        return [
            'column' => $columnBegining,
            'ligne' => $ligneBegining
        ];
    }

    /**
     * @param $sheet
     * @param $arrayName
     * @param $columnEnd
     * @param $ligneBegining
     */
    public function saveArrayPositionBegining($sheet, $arrayName, $columnEnd, $ligneBegining)
    {
        if (!isset($this->arrayPositionPositionBeginingBySheet[$sheet])) {
            $this->arrayPositionPositionBeginingBySheet[$sheet] = array();
        }
        $this->arrayPositionPositionBeginingBySheet[$sheet][$arrayName] = [
            'column' => ord($columnEnd),
            'ligne' => $ligneBegining
        ];
    }

    /**
     * @param $sheet
     * @param $arrayName
     * @return array
     */
    public function getArrayPositionBegining($sheet, $arrayName)
    {
        if (isset($this->arrayPositionPositionBeginingBySheet[$sheet]) && isset($this->arrayPositionPositionBeginingBySheet[$sheet][$arrayName])) {
            return $this->arrayPositionPositionBeginingBySheet[$sheet][$arrayName];
        }
        else {
            return [
                'column' => "A",
                'ligne' => $this->positionInSheets[$sheet]
            ];
        }
    }

    /**
     * @param $arrayType
     * @param $columnBegining
     * @param $columnEnd
     * @param $ligneBegining
     * @param $ligneEnd
     * @param $sheet
     */
    public function setArrayStyle($arrayType, $columnBegining, $columnEnd, $ligneBegining, $ligneEnd, $sheet)
    {
        if ($arrayType === 3) {
            // bold col titles
            $this->setStyleToCells($sheet, "$columnBegining$ligneBegining:$columnEnd$ligneEnd", array('borders' => array('allborders' => array('style' => \PHPExcel_Style_Border::BORDER_THIN))));
        }
        else {
            if ($arrayType !== 2) {
                // add bold for col titles
                $this->setStyleToCells($sheet, "$columnBegining$ligneBegining:$columnBegining$ligneEnd", array('font' => array('bold' => true)));

                // add border for ligne titles
                $this->setStyleToCells($sheet, "$columnBegining$ligneBegining:$columnEnd$ligneBegining", array('borders' => array('allborders' => array('style' => \PHPExcel_Style_Border::BORDER_THIN))));

                // add bold for col totals
                $this->setStyleToCells($sheet, "$columnBegining$ligneEnd:$columnEnd$ligneEnd", array('font' => array('bold' => true)));

                // add bolder for ligne totals
                $this->setStyleToCells($sheet, "$columnEnd$ligneBegining:$columnEnd$ligneEnd", array('font' => array('bold' => true)));

                // add border for title ligne
                $this->setStyleToCells($sheet, "$columnBegining$ligneEnd:$columnEnd$ligneEnd", array('borders' => array('allborders' => array('style' => \PHPExcel_Style_Border::BORDER_THIN))));
            }
            // add bold for ligne titles
            $this->setStyleToCells($sheet, "$columnBegining$ligneBegining:$columnEnd$ligneBegining", array('font' => array('bold' => true)));
        }

        // add alternative font per ligne
        for ($i = 0; $i <= $ligneEnd - $ligneBegining; $i++) {
            if ($i % 2) {
                $ligne = $ligneBegining + $i;
                $ews = $this->getSheet($sheet);
                $ews->getStyle("$columnBegining$ligne:$columnEnd$ligne")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('C4C4C4');
            }
        }
    }

    /**
     * @param $sheet
     * @param $cells
     * @param $style
     * @throws \PHPExcel_Exception
     */
    public function setStyleToCells($sheet, $cells, $style)
    {
        $ews = $this->getSheet($sheet);
        $ews->getStyle($cells)->applyFromArray($style);
    }

    /**
     * @param $value
     * @param $sheet
     */
    public function setCrossTable($key, $value, $sheet, $columnBegining)
    {
        $ews = $this->getSheet($sheet);
        $column = ord($columnBegining);
        $ews->setCellValue(chr($column) . strval($this->positionInSheets[$sheet]++), "$key");
        $ligne = $this->positionInSheets[$sheet];
        $cptTitle = 0;
        foreach ($value['cols'] as $col) {
            // set col title
            // +1 is because the first ligne cell is empty to permit ligne titles
            $ews->setCellValue(chr($column + $cptTitle++ + 1) . strval($ligne), $col['label']);
        }

        $rowLigneCpt = 1;
        foreach ($value['rows'] as $key=>$row) {
            $rowColumnCpt = 0;
            // set ligne title
            $ews->setCellValue(chr($column + $rowColumnCpt) . strval($ligne + $rowLigneCpt), $row['label']);
            foreach ($row['data'] as $data) {
                // set value
                $ews->setCellValue(chr($column + ++$rowColumnCpt) . strval($ligne + $rowLigneCpt), $data['value']);
            }
            $rowLigneCpt++;
        }
        // add sum formlulas to lignes and columns
        $this->addSumFormulaToTable($sheet, $column, $column + $rowColumnCpt, $ligne, $ligne + $rowLigneCpt);
        // register the max nb of rows for autosize
        $this->setMaxNbRowBySheet($sheet, $rowLigneCpt);
        // keep position in the sheet
        $this->positionInSheets[$sheet] += $rowLigneCpt + 1;

        $this->setArrayStyle(0, chr($column), chr($column + $cptTitle + 1), $ligne, $ligne + $rowLigneCpt, $sheet);
    }

    /**
     * @param $value
     * @param $sheet
     */
    public function setAggregatedTable($value, $sheet, $columnBegining)
    {
        $ews = $this->getSheet($sheet);
        $column = ord($columnBegining);
        $ligne = $this->positionInSheets[$sheet];
        $cptColumnTitle = 0;
        $cptRow = 0;

        // the aggregated table is passed to time for an array
        // first time is for col titles
        // second times for ligne titles and values
        $hasDatas = false;
        $datasColumnsCpt = 0;
        foreach ($value as $array) {
            if (!isset($array['data'])) {
                if (isset($array['label'])) {
                    $ews->setCellValue(chr($column + 1 + $cptColumnTitle) . strval($ligne), $array['label']);
                    $cptColumnTitle++;
                }
            }
            else {
                $hasDatas = true;
                $ews->setCellValue(chr($column) . strval($ligne + $cptRow), $array['label']);
                $cptDataColumn = 0;
                foreach ($array['data'] as $data) {
                    $ews->setCellValue(chr($column + ++$cptDataColumn) . strval($ligne + $cptRow), $data['value']);
                }
                $datasColumnsCpt = $cptDataColumn;
            }
            $cptRow++;
        }
        // add sum formlulas to lignes and columns
        if ($hasDatas) {
            $this->addSumFormulaToTable($sheet, $column, $column + $datasColumnsCpt, $ligne - 1, $ligne + $cptRow);
        }
        // register the max nb of rows for autosize
        $this->setMaxNbRowBySheet($sheet, $cptRow);
        // keep position in the sheet
        $this->positionInSheets[$sheet] += $cptRow - $cptColumnTitle + ($hasDatas ? 0 : 1);
        // return column end
        return chr($column + $datasColumnsCpt + 1);
    }

    /**
     * @param $value
     * @param $sheet
     */
    public function setEntityTable($value, $sheet, $setTitles, $columnBegining)
    {
        $ews = $this->getSheet($sheet);
        $column = ord($columnBegining);
        $propertyCpt = 0;
        foreach ($value as $property => $val) {
            // transform value into a string if it is an array
            if (is_array($val)) {
                $stringVal = "";
                foreach ($val as $part) {
                    if (!empty($stringVal)) {
                        $stringVal += ', ';
                    }
                    $stringVal .= (string)$part;
                }
                $val = $stringVal;
            }
            // set title property
            if ($setTitles) {
                $ews->setCellValue(chr($column + $propertyCpt) . strval($this->positionInSheets[$sheet]), $property);
            }
            // set property value
            $ews->setCellValue(chr($column + $propertyCpt) . strval($this->positionInSheets[$sheet] + ($setTitles ? 1 : 0)), $val);
            $propertyCpt++;
        }
        // register the max nb of rows for autosize
        $this->setMaxNbRowBySheet($sheet, $propertyCpt);
        $this->positionInSheets[$sheet] += ($setTitles ? 2 : 1);
        // return column end
        return chr($column + $propertyCpt - 1);
    }

    /**
     * @param $sheet
     * @param $key
     * @param $value
     */
    public function setSummaryTable($sheet, $key, $value, $column)
    {
        $ews = $this->getSheet($sheet);
        // set title if $key is a string
        if (is_string($key)) {
            // set title
            $ews->setCellValue($column . strval($this->positionInSheets[$sheet]), $key);
        }
        // set value
        $ews->setCellValue(chr(ord($column) + (is_string($key) ? 1 : 0)) . strval($this->positionInSheets[$sheet]++), $value);
        // register the max nb of rows for autosize
        $this->setMaxNbRowBySheet($sheet, ord($column) - 65 + (is_string($key) ? 2 : 1));
        // return column end
        return chr(ord($column) + (is_string($key) ? 1 : 0));
    }

    /**
     * @param $sheet
     * @param $columnBegining
     * @param $columnEnd
     * @param $ligne
     * @param $ligneEnd
     */
    public function addSumFormulaToTable($sheet, $columnBegining, $columnEnd, $ligne, $ligneEnd)
    {
        $ews = $this->getSheet($sheet);

        // add total title to cols
        $ews->setCellValue(chr($columnBegining) . strval($ligneEnd), "Total");
        // add cols sums
        for ($col = $columnBegining + 1; $col <= $columnEnd; $col++) {
            $ews->setCellValue(chr($col) . strval($ligneEnd), "=SUM(".chr($col).strval($ligne + 1).":".chr($col).strval($ligneEnd - 1).")");
        }

        // add total title to lignes
        $ews->setCellValue(chr($columnEnd + 1) . strval($ligne), "Total");
        // add ligne sums
        for ($ligne = $ligne + 1; $ligne < $ligneEnd; $ligne++) {
            $ews->setCellValue(chr($columnEnd + 1) . strval($ligne), "=SUM(".chr($columnBegining + 1).strval($ligne).":".chr($columnEnd).strval($ligne).")");
        }
        // cell sum of all values
        $ews->setCellValue(chr($columnEnd + 1) . strval($ligneEnd), "=SUM(".chr($columnBegining + 1).strval($ligneEnd).":".chr($columnEnd).strval($ligneEnd).")");

        // save position in sheet
        $this->positionInSheets[$sheet]++;
    }

    /**
     * @param $sheet
     */
    public function autosizeColumns($sheet)
    {
        if (isset($this->nbRowBySheet[$sheet])) {
            $ews = $this->getSheet($sheet);
            for ($col = 0; $col <= $this->nbRowBySheet[$sheet]; $col++) {
                $ews->getColumnDimension(chr($col + 65))->setAutoSize(true);
            }
        }
    }

    /**
     * @param $sheet
     * @param $nbRow
     */
    public function setMaxNbRowBySheet($sheet, $nbRow)
    {
        if (!isset($this->nbRowBySheet[$sheet])) {
            $this->nbRowBySheet[$sheet] = 1;
        }
        $this->nbRowBySheet[$sheet] = ($this->nbRowBySheet[$sheet] < $nbRow ? $nbRow : $this->nbRowBySheet[$sheet]);
    }

    /**
     * @param $array
     * @return bool
     */
    public function is_empty($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                return $this->is_empty($value);
            }
            return empty($array);
        }
        else {
            return empty($array);
        }
    }

    /**
     * @param string $title
     * @param string $creator
     * @param string $description
     * @param string $tags
     */
    public function createXls($title = null, $creator = null, $description = null, $tags = null)
    {
        $this->ea = new \PHPExcel();
        $this->ea->getProperties()
            ->setTitle($title)
            ->setCreator($creator)
            ->setDescription($description)
            ->setKeywords($tags);
    }

    /**
     * @param $sheet
     * @return \PHPExcel_Worksheet
     * @throws \PHPExcel_Exception
     */
    protected function getSheet($sheet)
    {
        $sheetCount = $this->ea->getSheetCount();
        if ($sheet >= $sheetCount) {
            $this->ea->createSheet($sheet);
        }

        if (!isset($this->positionInSheets[$sheet])) {
            $this->positionInSheets[$sheet] = 1;
        }

        return $this->ea->getSheet($sheet);
    }

    /**
     * @param \PHPExcel_Worksheet $ews
     * @param array $array
     * @param string $ligne
     */
    public function setHeaderArray($sheet, $arrayName, $columnBegining, $ligneBegining)
    {
        $ews = $this->getSheet($sheet);
        $ews->setCellValue("$columnBegining$ligneBegining", $arrayName);

        // bold and center titles
        $header = "$columnBegining$ligneBegining";
        $style = array(
            'font' => array('bold' => true),
            'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
        );
        $ews->getStyle($header)->applyFromArray($style);
    }

    /**
     * @param $name
     * @throws \PHPExcel_Reader_Exception
     */
    public function saveFile($name, $filePath = "", $format = "Excel2007")
    {
        $writer = \PHPExcel_IOFactory::createWriter($this->ea, $format);
        $writer->setIncludeCharts(true);
        $extention = $this->getExtentionFromFormat($format);
        $writer->save("$filePath$name.$extention");
        return "$filePath$name.$extention";
    }

    /**
     * @param $format
     * @return string
     */
    public function getExtentionFromFormat($format)
    {
        if (isset($this->formatExtentions[$format])) {
            return $this->formatExtentions[$format];
        }
        return 'xls';
    }
}