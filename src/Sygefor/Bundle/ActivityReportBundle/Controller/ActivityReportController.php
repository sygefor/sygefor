<?php

namespace Sygefor\Bundle\ActivityReportBundle\Controller;


use Sygefor\Bundle\ActivityReportBundle\Service\ActivityReportBuilder;
use Sygefor\Bundle\ActivityReportBundle\Service\ActivityReportBuilderFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ActivityReportController
 * @package Sygefor\Bundle\ActivityReportBundle\Controller
 * @Route("/report")
 *
 * IMPORTANT ELASTICSEARCH 2.0 IS REQUIRED
 */
class ActivityReportController extends Controller
{
    protected $multipleSessionTrainingTypes;

    protected $singleSessionTrainingTypes;

    public function __construct()
    {
        $this->multipleSessionTrainingTypes = array();
        $this->singleSessionTrainingTypes = array();
    }

    /**
     * @param Request $request
     * @Route("", name="report.index", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View()
     *
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        $multipleSessionTraining = $this->multipleSessionTrainingAction($request);
        $singleSessionTraining = $this->singleSessionTrainingAction($request);

        return array_merge_recursive($multipleSessionTraining, $singleSessionTraining);
    }

    /**
     * @Route("/training", name="report.training", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View()
     */
    public function multipleSessionTrainingAction(Request $request)
    {
        foreach ($this->get('sygefor_training.type.registry')->getTypes() as $type => $entity) {
            switch (get_parent_class($entity['class'])) {
                case 'Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining':
                    $this->multipleSessionTrainingTypes[$type] = $entity['label'];
                    break;
                case 'Sygefor\Bundle\TrainingBundle\Entity\Training\SingleSessionTraining':
                    $this->singleSessionTrainingTypes[$type] = $entity['label'];
                    break;
                default:
                    break;
            }
        }

        /** @var ActivityReportBuilderFactory $factory */
        $factory = $this->get('sygefor_activity_report.builder_factory');
        $builder = $factory->getBuilder($request);
        $array = array(
            "summaries" => $builder->getSummaries($this->multipleSessionTrainingTypes),
            "crosstabs" => $builder->getTrainingCrosstabs($this->multipleSessionTrainingTypes, $this->singleSessionTrainingTypes),
            "listings" => $builder->getListing($this->multipleSessionTrainingTypes)
        );

        $array['listings'] = array_merge($array['listings'], $builder->getListing($this->singleSessionTrainingTypes, array(
            'trainingKeys' => array(
                'id',
                'name',
                'dateBegin',
                'theme',
                'category',
                'totalCost',
                'national',
                'partners',
                'totalCost',
                'totalTaking',
                'maximiumNumberOfRegistration'
            ),
            'sumKeys' => array(
                'numberOfRegistrations',
                'numberOfParticipants',
                'totalCost'
            )
        )));

        return $array;
    }

    /**
     * @Route("/meeting", name="report.meeting", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View()
     */
    public function singleSessionTrainingAction(Request $request)
    {
        /** @var ActivityReportBuilderFactory $factory */
        $factory = $this->get('sygefor_activity_report.builder_factory');
        $builder = $factory->getBuilder($request);

        return array(
            "summaries" => $builder->getSummaries($this->singleSessionTrainingTypes),
            "crosstabs" => $builder->getSingleSessionTrainingCrosstabs($this->singleSessionTrainingTypes, $this->multipleSessionTrainingTypes)
        );
    }

    /**
     * @Route("/download/{format}", name="report.download", options={"expose"=true})
     * @Rest\View()
     */
    public function downloadAction(Request $request, $format)
    {
        $query = $request->query->all();
        $debug = FALSE;
        if (isset($query['debug'])) {
            $debug = TRUE;
            unset($query['debug']);
        }
        $request->request->replace($query);
        $data = $this->indexAction($request);

        // compute filters to get label
        $var = array();
        $params = $request->query->all();
        if (isset($params['filter']['and'])) {
            foreach ($params['filter']['and'] as $filter) {
                $key = current(array_keys($filter['term']));
                $value = current($filter['term']);
                $var[$key] = $value;
            }
        }

        $infos = array();
        if (isset($var['training.organization.name.source'])) {
            $infos[] = $var['training.organization.name.source'];
        }
        if (isset($var['semester'])) {
            $semester = ($var['semester'] == 1 ? '1er' : '2nd') . " semestre";
            if (isset($var['year']) && $var['year']) {
                $semester .= ' ' . $var['year'];
            }
            $infos[] = "Bilan d'activité du " . $semester;
        }
        else {
            if (isset($var['year'])) {
                $infos[] = "Bilan d'activité de l'année " . $var['year'];
            }
        }
        if (empty($infos)) {
            $infos[] = "Bilan d'activité général";
        }

        $variables = array(
            'infos' => $infos,
            'organization' => $this->getUser()->getOrganization(),
            'report' => $data
        );

        // handle debug
        if ($debug) {
            return new Response($html);
        }

        if ($format === "xls") {
            $filePath = $this->get('sygefor_excel_writer')->getXls(
                $this->get('sygefor_xls_paginer')->refactorDatas($variables),
                uniqid('Bilans_Sygefor3_'),
                sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sygefor' . DIRECTORY_SEPARATOR,
                'Sygefor3', 'Bilans des formations de Sygefor3',
                'bilan excel sygefor'
            );

            $content = file_get_contents($filePath);
            unlink($filePath);

            return new Response(
                $content,
                200,
                array(
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=cp-1252',
                    'Content-Disposition' => 'attachment; filename="Bilans_formations_Sygefor3.xlsx"',
                    'Content-Transfer-Encoding' => 'application/octet-stream',
                    'Content-Length' => strlen($content)
                )
            );
        }

        $header = $this->renderView('SygeforActivityReportBundle::header.html.twig', $variables);
        $footer = $this->renderView('SygeforActivityReportBundle::footer.html.twig', $variables);
        $html = $this->renderView('SygeforActivityReportBundle::report.html.twig', $variables);
        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'orientation' => 'Landscape',
                // header
                'header-html' => $header,
                'header-spacing' => 10,
                // this include 10mm between header and content
                'margin-top' => 20,
                // grow margin-top to handle header-spacing shifting,
                // footer
                'footer-html' => $footer,
                'footer-spacing' => 10,
                // this include 10mm between header and content
                'margin-bottom' => 20,
                // grow margin-top to handle header-spacing shifting,
                'dpi' => 96
                //'zoom' => (96/75) // @see https://github.com/wkhtmltopdf/wkhtmltopdf/issues/2156
            )), 200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="activity_report.pdf"'
            )
        );
    }
}
