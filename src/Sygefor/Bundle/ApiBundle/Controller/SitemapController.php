<?php

namespace Sygefor\Bundle\ApiBundle\Controller;

use Elastica\Filter\Bool;
use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Sygefor\Bundle\ApiBundle\Sitemap\Url;
use Sygefor\Bundle\ApiBundle\Sitemap\UrlSet;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;



class SitemapController extends Controller
{
    /**
     * @Route("/api/sitemap", name="api.sitemap", defaults={"_format" = "xml"})
     * @Rest\View
     */
    public function sitemapAction(Request $request)
    {
        $siteUrl = $this->container->getParameter("front_url")."/";

        $urlset = new UrlSet();

        $organizations = $this->getDoctrine()->getRepository('SygeforCoreBundle:Organization')->findAll();

        //program
        foreach ($organizations as $org) {
            $url = new Url($siteUrl .  "program/" . $org->getCode());
            $urlset->addUrl($url);
        }

        //formations
        $urlset->addUrl(new Url($siteUrl . "training"));

        //formateurs
        $urlset->addUrl(new Url($siteUrl . "trainers/extern"));
        $urlset->addUrl(new Url($siteUrl . "trainers/urfist"));

        //partenaires
        $urlset->addUrl(new Url($siteUrl . "partners"));

        //faq
        $urlset->addUrl(new Url($siteUrl . "faq"));

        //about
        $urlset->addUrl(new Url($siteUrl . "sygefor3"));

        //mentions légales
        $urlset->addUrl(new Url($siteUrl . "legals"));

        //association
        $urlset->addUrl(new Url($siteUrl . "aru"));


        /** @var SearchService $search */
        $search =  $this->get('sygefor_training.search');

        //getting only trainings with opened registrations
        $filter = new Term(array('sessions.displayOnline' => true));
        $search->filterQuery($filter);
        $search->setSize(9999);

        $response = $search->search();

        foreach($response['items'] as $item) {
            $url = new Url( $siteUrl . 'training/' . $item['id']);
            $urlset->addUrl($url);
        }

        return $urlset;
    }

}