<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 09/03/2015
 * Time: 12:19.
 */

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManager;
use Liuggio\ExcelBundle\Factory;
use AppBundle\Entity\Inscription;
use AppBundle\Entity\Session\Session;
use AppBundle\Entity\Session\Participation;
use AppBundle\Entity\Term\Evaluation\Theme;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Term\Evaluation\Criterion;
use AppBundle\Entity\Evaluation\NotedCriterion;
use AppBundle\Entity\Evaluation\EvaluatedTheme;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EvaluationSheet
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Factory
     */
    private $phpExcel;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var \PHPExcel
     */
    private $phpExcelObject;

    /**
     * EvaluationSheet constructor.
     *
     * @param ContainerInterface $container
     * @param Factory            $phpExcel
     * @param Session            $session
     */
    public function __construct(ContainerInterface $container, Factory $phpExcel, Session $session)
    {
        $this->container = $container;
        $this->phpExcel = $phpExcel;
        $this->session = $session;
    }

    /**
     * Builds balance sheet and returns the file through response.
     *
     * @return Response
     */
    public function getResponse()
    {
        $this->collectEvaluations();

        $writer = $this->phpExcel->createWriter($this->phpExcelObject, 'Excel5');

        $response = $this->phpExcel->createStreamedResponse($writer);
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=evaluations_session_'.strtolower($this->session->getId()).'.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }

    /**
     * Computes data over all sessions.
     *
     * @return array
     */
    private function collectEvaluations()
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();
        $inscriptions = $this->session->getInscriptions();
        $maxColumn = 'A';

        $this->phpExcelObject = $this->phpExcel->createPHPExcelObject();
        $this->phpExcelObject->getProperties()->setCreator('Sygefor')
            ->setTitle('Evaluations')
            ->setSubject($this->session->getTraining()->getName());
        $activeSheet = $this->phpExcelObject->setActiveSheetIndex(0);
        $this->setHeaders($em, $activeSheet);

        $trainers = array();
        $evalCount = 2;
        /** @var Participation $participation */
        foreach ($this->session->getParticipations() as $participation) {
            $trainers[] = $participation->getTrainer()->getFullName();
        }

        $evaluationThemes = $em->getRepository(Theme::class)->findBy(array(), array(Theme::orderBy() => 'ASC'));
        $evaluationCriterion = array();
        foreach ($evaluationThemes as $theme) {
            $evaluationCriterion[$theme->getId()] = $em->getRepository(Criterion::class)->findBy(array('theme' => $theme), array(Criterion::orderBy() => 'ASC'));
        }

        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            if (!$inscription->getEvaluation()) {
                continue;
            }

            // retrieve column values for each evaluation
            $values = [
                $this->session->getYear(),
                $this->session->getSemester(),
                $this->session->getTraining()->getTrainingCode(),
                $this->session->getTraining()->getName(),
                $this->session->getDateRange(),
                ($this->session->getPlace() ? $this->session->getPlace()->getName() : null),
            ];

            $evaluation = $inscription->getEvaluation();
            foreach ($evaluationThemes as $evaluationTheme) {
                $themeFound = false;
                /** @var EvaluatedTheme $evaluatedTheme */
                foreach ($evaluation->getThemes() as $evaluatedTheme) {
                    if ($evaluatedTheme->getTheme()->getId() === $evaluationTheme->getId()) {
                        $themeFound = true;
                        $values[] = $evaluatedTheme->getComments();
                        foreach ($evaluationCriterion[$evaluationTheme->getId()] as $evaluationCriteria) {
                            $criteriaFound = false;
                            /** @var NotedCriterion $notedCriteria */
                            foreach ($evaluatedTheme->getCriteria() as $notedCriteria) {
                                if ($evaluationCriteria->getId() === $notedCriteria->getCriterion()->getId()) {
                                    $values[] = $notedCriteria->getNote();
                                    $criteriaFound = true;
                                }
                            }
                            if (!$criteriaFound) {
                                $values[] = null;
                            }
                        }
                    }
                }
                if (!$themeFound) {
                    $values[] = null;
                    foreach ($evaluationCriterion[$evaluationTheme->getId()] as $evaluationCriteria) {
                        $values[] = null;
                    }
                }
            }

            $values[] = $evaluation->getGoodPoints();
            $values[] = $evaluation->getBadPoints();
            $values[] = $evaluation->getSuggestions();
            $maxColumn = $this->writeLine($activeSheet, $values, $evalCount++);
        }

        //global default cell styles
        $styleArray = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 10,
            ),
        );

        $activeSheet->getStyle('A1:'.$maxColumn.$evalCount)->applyFromArray($styleArray);
    }

    /**
     * @param EntityManager       $em
     * @param \PHPExcel_Worksheet $activeSheet
     *
     * @return int
     */
    protected function setHeaders($em, $activeSheet)
    {
        // get column names
        $columns = ['Année', 'Semestre', 'Numéro stage', 'Stage', 'Dates', 'Lieu'];

        // add evaluation theme, critaria and comment column names
        $evaluationThemes = $em->getRepository(Theme::class)->findBy(array(), array(Theme::orderBy() => 'ASC'));
        $evaluationCriterion = array();
        foreach ($evaluationThemes as $theme) {
            $evaluationCriterion[$theme->getId()] = $em->getRepository(Criterion::class)->findBy(array('theme' => $theme), array(Criterion::orderBy() => 'ASC'));
        }
        foreach ($evaluationThemes as $evaluationTheme) {
            $columns[] = $evaluationTheme->getName();
            /** @var Criterion $criterion */
            foreach ($evaluationCriterion[$evaluationTheme->getId()] as $criterion) {
                if ($criterion->getTheme()->getId() === $evaluationTheme->getId()) {
                    $columns[] = $criterion->getName();
                }
            }
        }
        $columns[] = 'Points forts';
        $columns[] = 'Points émliorables';
        $columns[] = 'Suggestions';
        $maxColumn = $this->writeLine($activeSheet, $columns);

        // set header styles
        $activeSheet->getRowDimension(1)
            ->setRowHeight(25);
        $activeSheet->getStyle('A1:'.$maxColumn)->applyFromArray(
            array(
                'font' => array(
                    'bold' => true,
                    'name' => 'Arial',
                    'size' => 10,
                    'color' => array('rgb' => 'FFFFFF'),
                ),
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '808080'),
                ),
                'borders' => array(
                    'bottom' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN, 'color' => array(
                            'rgb' => '000000',
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * @param \PHPExcel_Worksheet $activeSheet
     * @param array               $line
     * @param int                 $row
     *
     * @return string
     */
    protected function writeLine($activeSheet, $line, $row = 1)
    {
        // set column names into Excel sheet
        $colPreffix = '';
        $col = 64;
        $i = 0;
        foreach ($line as $cell) {
            $col = $col + 1;
            // if col > A, AA, BA, etc
            if ($col >= (65 + 26)) {
                if (empty($colPreffix)) {
                    $colPreffix = 'A';
                } else {
                    $colPreffix = chr(ord($colPreffix) + 1);
                }
                $col -= 26;
            }
            $activeSheet->setCellValue($colPreffix.chr($col).''.$row, $cell);
            $activeSheet->getColumnDimension($colPreffix.chr($col))->setAutoSize(true);
            ++$i;
        }

        return $colPreffix.chr($col).''.$row;
    }
}
