<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 16/04/14
 * Time: 11:20
 */

namespace Sygefor\Bundle\TrainingBundle\Tests\Controller;


use Sygefor\Bundle\CoreBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Client as Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\TrainingBundle\Controller\SessionController;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\UserBundle\Entity\User;

/**
 * Class TrainingControllerTest
 * @package Sygefor\Bundle\TrainingBundle\Tests\Controller
 */
class TrainingControllerTest extends WebTestCase
{
    /**
     *
     */
    public function testCreateTraining()
    {
        $organization = $this->client->getContainer()->get('doctrine')->getManager()->getRepository('SygeforCoreBundle:Organization')->findOneBy(array());
        $theme = $this->client->getContainer()->get('doctrine')->getManager()->getRepository("Sygefor\\Bundle\\TrainingBundle\\Entity\\Term\\Theme")->findOneBy(array());
        $this->createTempUser('view_user_all_form_rights', array ('sygefor_training.rights.training.all' ) );

        //cycling over existing types
        $types = array ('internship', 'meeting', 'diverse_training', 'training_course') ;
        foreach ($types as $type) {
            $url = $this->client->getContainer()->get('router')->generate('training.create', array('type' => $type));

            //connecting with non allowed role provokes unsuccessful response
            $this->loginWithRoles(array('ROLE_USER'));
            $this->client->request('GET', $url);
            $this->assertResponseSuccess(false);

            //login with right role -> response successful
            $this->loginAs('view_user_all_form_rights');
            $crawler = $this->client->request('GET', $url);

            $this->assertResponseSuccess(true);


            $specificFields = array();
            $fType="";
            if ($type == 'diverse_training') {
                $fType = 'diversetrainingtype';
            } else if ($type == 'internship') {
                $fType = "internshiptype" ;

            } else if ($type == 'meeting') {
                $fType = "meetingtype" ;
                $specificFields = array(
                    'eventType' => $this->client->getContainer()->get('doctrine')->getManager()->getRepository("Sygefor\\Bundle\\TrainingBundle\\Entity\\Term\\EventType")->findOneBy(array())->getId(),
                    'eventKind' => $this->client->getContainer()->get('doctrine')->getManager()->getRepository("Sygefor\\Bundle\\TrainingBundle\\Entity\\Term\\EventKind")->findOneBy(array())->getId(),
                    'session' => array(
                        'dateBegin' => '12/05/2015',
                        'dateEnd' => '14/05/2015',
                        'limitRegistrationDate' => '10/05/2015',
                        'maximumNumberOfRegistrations' => '10',
                        'published' => '1'
                    )
                );
            } else if ($type == 'training_course') {
                $fType = "trainingcoursetype" ;
            } else {
                $this->assertFalse(true);
            }

            $csrfToken = $crawler->filter('input[name="'.$fType.'[_token]"]')->extract(array('value'))[0];
            //posting request
            $this->client->request(
                'POST',
                $url,
                array($fType => array_merge(array(
                    'organization' => $organization->getId(),
                    'theme' => $theme->getId(),
                    'name' => 'name',
                    'objectives' => "plop",
                    'program' => "gros programme",
                    'firstSessionPeriodSemester'=> 2,
                    'firstSessionPeriodYear'=>2009,
                    "_token" => $csrfToken
                ),
                $specificFields
                ))
            );

            $this->assertTrue($this->client->getResponse()->isRedirect($this->client->getContainer()->get('router')->generate('training.index')));
        }
    }

    /**
     *
     */
    public function testRemoveTraining()
    {
        $repository = $this->getEntityManager()->getRepository('SygeforTrainingBundle:Training');
        $training = $repository->findOneBy(array());

        $url = $this->client->getContainer()->get('router')->generate('training.remove', array('id' => $training->getId()));

        $this->client->request('GET', $url);
        //$this->markTestSkipped('Weird error about cascade persist : to be fixed !');

        $this->assertResponseSuccess(false);

        $this->createTempUser('view_user_all_form_rights', array ('sygefor_training.rights.training.all' ) );
        $this->loginAs('view_user_all_form_rights');
        $this->client->request('GET', $url);
        $this->assertResponseSuccess(true);

        $this->client->request('POST', $url);

        $this->assertResponseRedirect(true);

        $repository->clear();
        $training = $repository->find($training->getId());
        $this->assertNull($training);
    }

}
