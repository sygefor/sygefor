<?php

namespace ActivityReportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ActivityReportBundle\Utils\ActivityReportBuilderFactory;

/**
 * Class ActivityReportController.
 *
 * @Route("/report")
 */
class ActivityReportController extends Controller
{
    /**
     * @param Request $request
     * @Route("", name="report.index", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View()
     *
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        /** @var ActivityReportBuilderFactory $factory */
        $factory = $this->get('sygefor_activity_report.builder_factory');
        $builder = $factory->getBuilder($request);

        $trainingTypes = array();
        foreach ($this->get('sygefor_core.registry.training_type')->getTypes() as $type => $entity) {
            $trainingTypes[$type] = $entity['label'];
        }

        $array = array(
            'summaries' => $builder->getSummaries($trainingTypes),
            'crosstabs' => $builder->getCrosstabs($trainingTypes),
            'listings' => $builder->getListing($trainingTypes),
        );

        if (count($array['summaries']) === 1) {
            $array['summaries']['all'] = $array['summaries'][array_keys($array['summaries'])[0]];
        }

        return $array;
    }

    /**
     * @Route("/download", name="report.download", options={"expose"=true})
     * @Rest\View()
     */
    public function downloadAction(Request $request)
    {
        return $this->downloadXlsBalanceSheet($this->getBalanceFileData($request));
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getBalanceFileData(Request $request)
    {
        // prepara elasticsearch query
        $query = $request->query->all();
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
            $semester = ($var['semester'] === 1 ? '1er' : '2nd').' semestre';
            if (isset($var['year']) && $var['year']) {
                $semester .= ' '.$var['year'];
            }
            $infos[] = "Bilan d'activité du ".$semester;
        } else {
            if (isset($var['year'])) {
                $infos[] = "Bilan d'activité de l'année ".$var['year'];
            }
        }
        if (empty($infos)) {
            $infos[] = "Bilan d'activité général";
        }

        $variables = array(
            'infos' => $infos,
            'organization' => $this->getUser()->getOrganization(),
            'report' => $data,
        );

        return $variables;
    }

    /**
     * @param $data
     *
     * @return Response
     */
    protected function downloadXlsBalanceSheet($data)
    {
        $filePath = $this->get('sygefor_excel_writer')->getXls(
            $this->get('sygefor_xls_paginer')->refactorDatas($data),
            uniqid('Bilans_Sygefor3_'),
            sys_get_temp_dir().DIRECTORY_SEPARATOR.'sygefor'.DIRECTORY_SEPARATOR,
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
                'Content-Length' => strlen($content),
            )
        );
    }

    /**
     * @param $data
     *
     * @return Response
     */
    protected function downloadPdfBalanceSheet($data)
    {
        $header = $this->renderView('ActivityReportBundle::header.html.twig', $data);
        $footer = $this->renderView('ActivityReportBundle::footer.html.twig', $data);
        $html = $this->renderView('ActivityReportBundle::report.html.twig', $data);

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
                'dpi' => 96,
                //'zoom' => (96/75) // @see https://github.com/wkhtmltopdf/wkhtmltopdf/issues/2156
            )), 200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Bilans_formations_Sygefor3.pdf"',
            )
        );
    }
}
