<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 09/03/2015
 * Time: 12:19.
 */
namespace Sygefor\Bundle\MyCompanyBundle\SpreadSheet;

use Liuggio\ExcelBundle\Factory;
use Sygefor\Bundle\MyCompanyBundle\Entity\EvaluationNotedCriterion;
use Sygefor\Bundle\MyCompanyBundle\Entity\Inscription;
use Sygefor\Bundle\MyCompanyBundle\Entity\Participation;
use Sygefor\Bundle\MyCompanyBundle\Entity\Session;
use Sygefor\Bundle\MyCompanyBundle\Entity\Term\EvaluationCriterion;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

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
     * @var Factory
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
        $this->phpExcel  = $phpExcel;
        $this->session   = $session;
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
        $response->headers->set('Content-Disposition', 'attachment;filename=evaluations_session_' . strtolower($this->session->getId()) . '.xls');
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
        $inscriptions = $this->session->getInscriptions();

        $this->phpExcelObject = $this->phpExcel->createPHPExcelObject();

        $this->phpExcelObject->getProperties()->setCreator('Sygefor')
            ->setTitle('Evaluations')
            ->setSubject($this->session->getTraining()->getName());

        /** @var \PHPExcel_Worksheet $activeSheet */
        $activeSheet = $this->phpExcelObject->setActiveSheetIndex(0);

        $first = 1;
        //building first row with labels
        $col = 'A';
        //A
        $activeSheet->setCellValue($col . '' . $first, 'Année');
        ++$col;
        //B
        $activeSheet->setCellValue($col . '' . $first, 'Semestre');
        ++$col;
        //C
        $activeSheet->setCellValue($col . '' . $first, 'Numéro stage');
        ++$col;
        //D
        $activeSheet->setCellValue($col . '' . $first, 'Stage');
        ++$col;
        //E
        $activeSheet->setCellValue($col . '' . $first, 'Date');
        ++$col;
        //F
        $activeSheet->setCellValue($col . '' . $first, 'Lieu');
        ++$col;

        $evaluationCriteria = $this->container->get('doctrine.orm.entity_manager')->getRepository(EvaluationCriterion::class)
            ->createQueryBuilder('ec')
            ->orderBy('ec.position', 'ASC')
            ->getQuery()->getResult();

        foreach ($evaluationCriteria as $criteria) {
            $activeSheet->setCellValue($col . '' . $first, $criteria->getName());
            ++$col;
        }
        $activeSheet->setCellValue($col . '' . $first, 'Remarques');

        $activeSheet->getRowDimension(1)
            ->setRowHeight(25);
        $activeSheet->getStyle('A1:' . $col . '1')->applyFromArray(
            array(
                'font' => array(
                    'bold'  => true,
                    'name'  => 'Arial',
                    'size'  => 10,
                    'color' => array('rgb' => 'FFFFFF'),
                ),
                'fill' => array(
                    'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
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

        $trainers  = array();
        $evalCount = 2;
        /** @var Participation $participation */
        foreach ($this->session->getParticipations() as $participation) {
            $trainers[] = $participation->getTrainer()->getFullName();
        }

        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            if ($inscription->getCriteria()->count() === 0) {
                continue;
            }

            $col = 'A';
            $activeSheet->getRowDimension($evalCount)->setRowHeight(15);

            //A
            $activeSheet->setCellValue($col . '' . $evalCount, $this->session->getYear());
            ++$col;

            //B
            $activeSheet->setCellValue($col . '' . $evalCount, $this->session->getSemester());
            ++$col;

            //C
            $activeSheet->setCellValue($col . '' . $evalCount, $this->session->getTraining()->getName());
            ++$col;
            $date = $this->session->getDateBegin();

            //D
            $activeSheet->setCellValue($col . '' . $evalCount, $date->format('d/m/Y'));
            ++$col;

            //F
            $activeSheet->setCellValue($col . '' . $evalCount, ($this->session->getPlace() ? $this->session->getPlace()->getName() : null));
            ++$col;

            /** @var EvaluationNotedCriterion $criterion */
            foreach ($inscription->getCriteria() as $criterion) {
                $activeSheet->setCellValue($col . '' . $evalCount, $criterion->getNote());
                ++$col;
            }
            $activeSheet->setCellValue($col . '' . $evalCount, $inscription->getMessage());

            ++$evalCount;
            $results[$inscription->getId()][$criterion->getCriterion()->getId()] = $criterion->getNote();
        }

        //global default cell styles
        $styleArray = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 10,
            ),

        );

        $activeSheet->getStyle('A1:' . $col . $evalCount)->applyFromArray($styleArray);
    }
}
