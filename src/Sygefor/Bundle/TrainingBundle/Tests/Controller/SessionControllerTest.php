<?php
namespace Sygefor\Bundle\TrainingBundle\Tests\Controller;

use Sygefor\Bundle\CoreBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Client as Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\TrainingBundle\Controller\SessionController;

/**
 * Class SessionControllerTest
 * @package Sygefor\Bundle\TrainingBundle\Tests\Controller
 */
class SessionControllerTest extends WebTestCase
{
    public function testCreateSession()
    {
        $training = $this->client->getContainer()->get('doctrine')->getManager()->getRepository('SygeforTrainingBundle:Training')->findOneBy(array());
        $url = $this->client->getContainer()->get('router')->generate('session.create', array('training' => $training->getId()));

        //connecting with non allowed role provokes unsuccessful response
        $this->client->request('GET', $url);
        $this->assertResponseSuccess(false);

        //login with right role -> response successful
        $this->loginWithRoles(array('ROLE_ADMIN'));
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseSuccess(true);

        $csrfToken = $crawler->filter('input[name="sessiontype[_token]"]')->extract(array('value'))[0];

        //posting request
        $this->client->request(
            'POST',
            $url,
            array('sessiontype' => array(
                'training' => $training->getId(),
                'dateBegin' => '16/04/2014'/*array(
                    'year' => '2014',
                    'month' => '4',
                    'day' => '16'
                )*/,
                'dateEnd' => '25/04/2014' /*array(
                    'year' => '2014',
                    'month' => '4',
                    'day' => '25'
                )*/,
                'limitRegistrationDate' => '06/04/2014' /*array(
                    'year' => '2014',
                    'month' => '4',
                    'day' => '16'
                )*/,
                'maximumNumberOfRegistrations' => 10,
                'published' => 1,
                "_token" => $csrfToken
            ))
        );
        $this->assertTrue($this->client->getResponse()->isRedirect($this->client->getContainer()->get('router')->generate('training.index')));
    }



    public function testRemoveSession()
    {
        $repository = $this->getEntityManager()->getRepository('SygeforTrainingBundle:Session');
        $session = $repository->findOneBy(array());
        $url = $this->client->getContainer()->get('router')->generate('session.remove', array('id' => $session->getId()));

        $this->client->request('GET', $url);
        $this->assertResponseSuccess(false);

        $this->loginWithRoles(array('ROLE_ADMIN'));
        $this->client->request('GET', $url);
        $this->assertResponseSuccess(true);

        $this->client->request('POST', $url);
        $this->assertResponseRedirect(true, $this->generateUrl('training.index'));

        $repository->clear();
        $session = $repository->find($session->getId());
        $this->assertNull($session);
    }

}
