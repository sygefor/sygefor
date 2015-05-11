<?php
namespace Sygefor\Bundle\CoreBundle\Tests\Controller;

use Sygefor\Bundle\CoreBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultControllerTest
 * @package Sygefor\Bundle\CoreBundle\Tests\Controller
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * testIndex
     */
    public function testIndex()
    {
        $url = $this->generateUrl('core.index');

        // redirect to login
        $this->getRequest($url);
        $this->assertResponseRedirectToLogin();

        $this->loginAsUser();
        $this->getRequest($url);
        $this->assertContentContains("<div ui-view></div>");
    }

    /**
     * testSearch
     */
    public function testSearch()
    {
        $url = $this->generateUrl('core.search');

        // redirect to login
        $this->getRequest($url);
        $this->assertResponseRedirectToLogin();

        // json response
        $this->loginAsUser();
        $this->jsonRequest('GET', $url);
        $this->assertResponseSuccess();
        $this->assertContentIsJson();
    }

    /**
     * testEntity
     */
    public function testEntity()
    {
        // get one training
        $repository = $this->getEntityManager()->getRepository('SygeforTrainingBundle:Training');
        $training = $repository->findOneBy(array());

        // build url
        $url = $this->generateUrl('core.entity', array(
            'class' => 'SygeforTrainingBundle:Training',
            'id' => $training->getId(),
        ));

        // redirect to login
        $this->getRequest($url);
        $this->assertResponseRedirectToLogin();

        // forbidden
        $this->loginAsUser();
        $this->jsonRequest('GET', $url);
        $this->assertResponseCode(Response::HTTP_FORBIDDEN);

        // temp user with right access
        $organization = $this->getEntityManager()->find('SygeforCoreBundle:Organization', $training->getOrganization()->getId());
        $this->createTempUser("user", array('sygefor_training.rights.training.own'), array('organization' => $organization));
        $this->loginAs("user");
        $this->jsonRequest('GET', $url);
        $this->assertResponseSuccess();
        $response = $this->getResponseJson();
        $this->assertEquals($training->getName(), $response->name);
    }
}
