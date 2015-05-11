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
 */
class ActivityReportController extends Controller
{
    /**
     * @Route("", name="report.index", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View()
     */
    public function indexAction(Request $request)
    {
        $training = $this->trainingAction($request);
        $meeting = $this->meetingAction($request);
        return array_merge_recursive($training, $meeting);
    }

    /**
     * @Route("/training", name="report.training", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View()
     */
    public function trainingAction(Request $request)
    {
        /** @var ActivityReportBuilderFactory $factory */
        $factory = $this->get('sygefor_activity_report.builder_factory');
        $builder = $factory->getBuilder($request);
        $types = array('internship', 'training_course', 'diverse_training');

        return array(
          "summaries" => $builder->getSummaries($types),
          "crosstabs" => $builder->getTrainingCrosstabs(),
          "listings" => $builder->getListing($types)
        );
    }

    /**
     * @Route("/meeting", name="report.meeting", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View()
     */
    public function meetingAction(Request $request)
    {
        /** @var ActivityReportBuilderFactory $factory */
        $factory = $this->get('sygefor_activity_report.builder_factory');
        $builder = $factory->getBuilder($request);
        $types = array('meeting');

        return array(
          "summaries" => $builder->getSummaries($types),
          "crosstabs" => $builder->getMeetingCrosstabs()
        );
    }

    /**
     * @Route("/download", name="report.download", options={"expose"=true})
     * @Rest\View()
     */
    public function downloadAction(Request $request)
    {
        $query = $request->query->all();
        $debug = false;
        if(isset($query['debug'])) {
            $debug = true;
            unset($query['debug']);
        }
        $request->request->replace($query);
        $data = $this->indexAction($request);

        // compute filters to get label
        $var = array();
        $params = $request->query->all();
        if(isset($params['filter']['and'])) {
            foreach($params['filter']['and'] as $filter) {
                $key = current(array_keys($filter['term']));
                $value = current($filter['term']);
                $var[$key] = $value;
            }
        }
        if(isset($var['training.organization.name.source'])) {
            $infos[] = $var['training.organization.name.source'];
        }
        if(isset($var['semester'])) {
            $semester = ($var['semester'] == 1 ? '1er' : '2nd') . " semestre";
            if($var['year']) {
                $semester .= ' ' . $var['year'];
            }
            $infos[] = "Bilan d'activité du " . $semester;
        } elseif(isset($var['year'])) {
            $infos[] = "Bilan d'activité de l'année " . $var['year'];
        }

        $variables = array(
          'infos' => $infos,
          'organization' => $this->getUser()->getOrganization(),
          'report' => $data
        );

        $header = $this->renderView('SygeforActivityReportBundle::header.html.twig', $variables);
        $footer = $this->renderView('SygeforActivityReportBundle::footer.html.twig', $variables);
        $html = $this->renderView('SygeforActivityReportBundle::report.html.twig', $variables);

        // handle debug
        if($debug) {
            return new Response($html);
        }

        return new Response(
          $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
              'orientation' => 'Landscape',
               // header
              'header-html' => $header,
              'header-spacing' => 10,   // this include 10mm between header and content
              'margin-top' => 20,        // grow margin-top to handle header-spacing shifting,
              // footer
              'footer-html' => $footer,
              'footer-spacing' => 10,   // this include 10mm between header and content
              'margin-bottom' => 20,        // grow margin-top to handle header-spacing shifting,
			  'dpi' => 96
              //'zoom' => (96/75) // @see https://github.com/wkhtmltopdf/wkhtmltopdf/issues/2156
          )), 200,
          array(
            'Content-Type'          => 'application/pdf',
            'Content-Disposition'   => 'attachment; filename="activity_report.pdf"'
          )
        );
    }

}
