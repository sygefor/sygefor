<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 09/03/2015
 * Time: 12:19
 */

namespace Sygefor\Bundle\TrainingBundle\SpreadSheet;


use Doctrine\ORM\Query;
use Sygefor\Bundle\TraineeBundle\Entity\Evaluation;
use Sygefor\Bundle\TraineeBundle\Entity\EvaluationNotedCriterion;

class EvaluationSheet
{
    /**
     * @var Session
     */
    private $session;

    /**
     * PHPExcel parser
     * @var \PHPExcel
     */
    private $phpExcel;

    /** @var  Container */
    private $container;


    /**
     * PHPExcel object
     * @var
     */
    private $phpExcelObject;

    /**
     * @param $training
     */
    public function __construct($session, $phpExcel, $container)
    {
        $this->session = $session;
        $this->phpExcel = $phpExcel;
        $this->container = $container;
    }

    /**
     * Builds balance sheet and returns the file through response.
     * @return Response
     */
    public function getResponse()
    {
        $this->collectEvaluationsInfos();

        $writer = $this->phpExcel->createWriter($this->phpExcelObject, 'Excel5');

        $response =  $this->phpExcel->createStreamedResponse($writer);
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=evaluations_session_'.strtolower($this->session->getId()).'.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        return $response;
    }

    /**
     * Computes data over all sessions
     * @return array
     */
    private function collectEvaluationsInfos ()
    {
        $evals = array();
        foreach ($this->session->getInscriptions() as $inscr) {
            $evals[] = $inscr->getEvaluation();
        }

        /** @var  phpExcelObject */
        $this->phpExcelObject = $this->phpExcel->createPHPExcelObject();

        $this->phpExcelObject->getProperties()->setCreator("Sygefor")
            ->setTitle("Evaluations")
            ->setSubject($this->session->getTraining()->getName());

        /** @var \PHPExcel_Worksheet $activeSheet */
        $activeSheet = $this->phpExcelObject->setActiveSheetIndex(0);


        list ($criters, $evaluations) = $this->getEvaluations($evals);

        $first = 1;
        //building first row with labels
        $col = 'A';
        //A
        $activeSheet->setCellValue($col.''.$first, "Année");$col++;
        //B
        $activeSheet->setCellValue($col.''.$first, "Semestre");$col++;
        //C
        $activeSheet->setCellValue($col.''.$first, "Numéro stage");$col++;
        //D
        $activeSheet->setCellValue($col.''.$first, "Stage");$col++;
        //E
        $activeSheet->setCellValue($col.''.$first, "Date");$col++;
        //F
        $activeSheet->setCellValue($col.''.$first, "Lieu");$col++;
        //G
        $activeSheet->setCellValue($col.''.$first, "Formateurs");$col++;
        //H
        $activeSheet->setCellValue($col.''.$first, "Nom");$col++;
        //I
        $activeSheet->setCellValue($col.''.$first, "Prénom");$col++;
        //J
        $activeSheet->setCellValue($col.''.$first, "Type de public");$col++;
        foreach($criters as $criter) {
            $activeSheet->setCellValue($col.''.$first, $criter->getName());$col++;
        }
        $activeSheet->setCellValue($col.''.$first, "Remarques");

        $activeSheet->getRowDimension(1)
            ->setRowHeight(25);
        $activeSheet->getStyle('A1:'.$col.'1')->applyFromArray(
            array(
                'font' => array(
                    'bold'  => true,
                    'name' => 'Arial',
                    'size' => 10,
                    'color'=> array('rgb'=>'FFFFFF'),
                ),
                'fill' => array (
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'808080')
                ),
                'borders' => array(
                    'bottom' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,'color' => array(
                            'rgb' => '000000'
                        )
                    )
                )
            )
        );


        $evalCount = 2;
        /** @var Evaluation $eval */
        foreach ($evals as $eval) {
            if (empty($eval)) continue;
            $col = "A";
            $activeSheet->getRowDimension($evalCount)
                ->setRowHeight(15);
            //A
            $activeSheet->setCellValue($col . '' . $evalCount, $eval->getInscription()->getSession()->getYear());
            $col++;
            //B
            $activeSheet->setCellValue($col . '' . $evalCount, $eval->getInscription()->getSession()->getSemester());
            $col++;
            //C
            $activeSheet->setCellValue($col . '' . $evalCount, $eval->getInscription()->getSession()->getTraining()->getNumber());
            $col++;
            //D
            $activeSheet->setCellValue($col . '' . $evalCount, $eval->getInscription()->getSession()->getTraining()->getName());
            $col++;
            $date = $eval->getInscription()->getSession()->getDateBegin();
            //E
            $activeSheet->setCellValue($col . '' . $evalCount, $date->format('d/m/Y'));
            $col++;
            //F
            $activeSheet->setCellValue($col . '' . $evalCount, $eval->getInscription()->getSession()->getPlace()->getName());
            $col++;

            $trainers = array();
            foreach ($this->session->getTrainers() as $trainer) {
                $trainers[] = $trainer->getFullName();
            }
            //G
            $activeSheet->setCellValue($col . '' . $evalCount, implode(", ", $trainers));
            $col++;

            //H
            $activeSheet->setCellValue($col . '' . $evalCount, $eval->getInscription()->getTrainee()->getFirstname());
            $col++;

            //I
            $activeSheet->setCellValue($col . '' . $evalCount, $eval->getInscription()->getTrainee()->getLastname());
            $col++;
            $pt = $eval->getInscription()->getTrainee()->getPublicType();

            //J
            $activeSheet->setCellValue($col . '' . $evalCount, (!empty($pt)) ? $pt->getName() : '');
            $col++;

            /** @var EvaluationNotedCriterion $criteria */
            //var_dump($evaluations);
            foreach ($criters as $criter) {
                $activeSheet->setCellValue($col . '' . $evalCount, (!empty($evaluations[$eval->getInscription()->getId()][$criter->getId()])) ? $evaluations[$eval->getInscription()->getId()][$criter->getId()] : '');
                $col++;
            }
            $activeSheet->setCellValue($col . '' . $evalCount, $eval->getMessage());

            $evalCount++;
        }

        //global default cell styles
        $styleArray = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 10
            ),

        );
        $activeSheet->getStyle('A1:'.$col.$evalCount)->applyFromArray($styleArray);

    }

    /**
     *
     */
    private function getEvaluations($evals)
    {
        $criters = $this->container->get('doctrine.orm.entity_manager')->getRepository('SygeforTraineeBundle:Term\EvaluationCriterion')->createQueryBuilder('c')//->select('c')
            ->orderBy('c.position', 'asc')->getQuery()->getResult();

        $results = array();
        foreach ($evals as $eval) {
            if (empty($eval)) continue;
            $results [$eval->getInscription()->getId()] = array();

            /** @var EvaluationNotedCriterion $criter */
            foreach ($eval->getCriteria() as $criter) {
                $results[$eval->getInscription()->getId()][$criter->getCriterion()->getId()] = $criter->getNote();
            }
        }
        return array($criters, $results);
    }

} 