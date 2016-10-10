<?php

namespace Sygefor\Bundle\TraineeBundle\Tests\Controller;

use Sygefor\Bundle\CoreBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TraineeControllerTest.
 */
class TraineeControllerTest extends WebTestCase
{
    /**
     * setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->createTempUser('user_own', array('sygefor_trainee.rights.trainee.all'));
        $this->createTempUser('user_all', array('sygefor_trainee.rights.trainee.own'));
    }

    /**
     * search action.
     */
    public function testSearch()
    {
        $url = $this->generateUrl('trainee.search');

        // redirect to login
        $this->getRequest($url);
        $this->assertResponseRedirectToLogin();

        // access denied
        $this->loginAsUser();
        $this->jsonRequest('GET', $url);
        $this->assertResponseCode(Response::HTTP_FORBIDDEN);

        // user : own trainee
        $this->loginAs('user_own');
        $this->jsonRequest('GET', $url);
        $this->assertResponseSuccess();
        $this->assertContentIsJson();

        // user : all trainee
        $this->loginAs('user_all');
        $this->jsonRequest('GET', $url);
        $this->assertResponseSuccess();
        $this->assertContentIsJson();
    }

    /**
     * view action.
     */
    public function testView()
    {
        $trainee = $this->getRepository('SygeforTraineeBundle:AbstractTrainee')->findOneBy(array());
        $url     = $this->generateUrl('trainee.view', array('id' => $trainee->getId()));

        // redirect to login
        $this->getRequest($url);
        $this->assertResponseRedirectToLogin();

        // access denied
        $this->loginAsUser();
        $this->jsonRequest('GET', $url);
        $this->assertResponseCode(Response::HTTP_FORBIDDEN);

        // user from same organization
        $organization = $this->getEntityManager()->find('SygeforCoreBundle:Organization', $trainee->getOrganization()->getId());
        $user         = $this->createTempUser('user', array('sygefor_trainee.rights.trainee.own'), array('organization' => $organization));
        $this->loginAs($user);
        $this->jsonRequest('GET', $url);
        $this->assertResponseSuccess();

        // user from other organization
        $qb = $this->getRepository('SygeforCoreBundle:Organization')->createQueryBuilder('o');
        $qb
          ->where('o.id != :identifier')->setParameter('identifier', $trainee->getOrganization()->getId())
          ->setMaxResults(1);
        $otherOrganization = $qb->getQuery()->getSingleResult();
        $user              = $this->createTempUser('user', array('sygefor_trainee.rights.trainee.own'), array('organization' => $otherOrganization));
        $this->loginAs($user);
        $this->jsonRequest('GET', $url);
        $this->assertResponseCode(Response::HTTP_FORBIDDEN);

        // user from other organization with all right
        $user = $this->createTempUser('user', array('sygefor_trainee.rights.trainee.all'), array('organization' => $otherOrganization));
        $this->loginAs($user);
        $this->jsonRequest('GET', $url);
        $this->assertResponseSuccess();

    }
}
