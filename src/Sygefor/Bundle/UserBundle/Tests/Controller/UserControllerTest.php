<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 24/03/14
 * Time: 16:22
 */

namespace Sygefor\Bundle\UserBundle\Tests\Controller;

use Sygefor\Bundle\UserBundle\Controller\UserController;
use Sygefor\Bundle\CoreBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Client as Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserControllerTest extends WebTestCase
{
    public function testAddUser()
    {
        //connecting with non allowed role provokes unsuccessful response
        $this->loginWithRoles(array('ROLE_USER'));

        /**
         * @var Crawler
         */
        $this->client->request('GET', $this->client->getContainer()->get('router')->generate('user.add', array(), false));
        $this->assertFalse($this->client->getResponse()->isSuccessful());

        //login with right role -> response successful
        $this->createTempUser('view_user_all_user_rights', array ('sygefor_user.rights.user.all' ) );
        $this->loginAs('view_user_all_user_rights');

        $crawler = $this->client->request('GET',
            $this->client->getContainer()->get('router')->generate('user.add', array(), false)
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $extract = $crawler->filter('input[name="user[_token]"]')
            ->extract(array('value'));
        //$csrfToken = $extract[0];

        //posting request with name
        $this->client->request(
            'POST',
            $this->client->getContainer()->get('router')->generate('user.add', array(), false),
            array('user'=>array(
                'username' => 'test'.uniqid(),
                'plainPassword' => 'plopiplop',
                'email' => 'plop'.uniqid().'@mail.com',
                'enabled' => '1',
                ))
        );
        $this->assertTrue($this->client->getResponse()->isRedirect($this->client->getContainer()->get('router')->generate('user.index')));
    }

    public function testEditUser()
    {
        /**
         * @var EntityManager $em
         */
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository('SygeforUserBundle:User');

        //getting random user to update it
        /** @var User $user */
        $user = $repo->findAll();
        $id = $user[0]->getId();

        //connecting with non allowed role provokes unsuccessful response
        $this->loginWithRoles(array('ROLE_USER'));

        $this->client->request('GET', $this->client->getContainer()->get('router')->generate('user.edit', array('id'=>$id), false));
        $this->assertFalse($this->client->getResponse()->isSuccessful());

        //connecting with allowed role provokes successful response
        $this->createTempUser('view_user_all_user_rights', array ('sygefor_user.rights.user.all' ) );
        $this->loginAs('view_user_all_user_rights');

        $this->client->request('GET', $this->client->getContainer()->get('router')->generate('user.edit', array('id'=> '1000000000' ), false));
        $this->assertFalse($this->client->getResponse()->isSuccessful());
        $crawler = $this->client->request('GET', $this->client->getContainer()->get('router')->generate('user.edit', array('id'=>$id), false));
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $extract = $crawler->filter('input[name="user[_token]"]')
            ->extract(array('value'));
        //$csrfToken = $extract[0];

        //posting request without password
        $this->client->request(
            'POST',
            $this->client->getContainer()->get('router')->generate('user.edit', array('id'=>$id), false),
            array('user'=>array(
                ))
        );

        $this->assertTrue($this->client->getResponse()->isRedirect($this->client->getContainer()->get('router')->generate('user.index')));

        //posting request with password
        $crawler = $this->client->request('GET', $this->client->getContainer()->get('router')->generate('user.edit', array('id'=>$id), false));
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->client->request(
            'POST',
            $this->client->getContainer()->get('router')->generate('user.edit', array('id'=>$id), false),
            array('user'=>array(
                'plainPassword' => array('first' => 'plopiplop', 'second' => 'plopiplop'),
                ))
        );

        $this->assertTrue($this->client->getResponse()->isRedirect($this->client->getContainer()->get('router')->generate('user.index')));
    }

    public function testRemoveUser()
    {
        /**
         * @var EntityManager $em
         */
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository('SygeforUserBundle:User');

        /** @var User $user */
        $user = $repo->findAll();

        $userId = $user[sizeof($user) - 1]->getId();

        //connecting with non allowed role provokes unsuccessful response
        $this->loginWithRoles(array('ROLE_USER'));

        /**
         * @var Crawler
         */

        $this->client->request('GET', $this->client->getContainer()->get('router')->generate('user.remove', array('id'=>$userId), false));
        $this->assertFalse($this->client->getResponse()->isSuccessful());

        //login with right role -> response successful
        $this->createTempUser('view_user_all_user_rights', array ('sygefor_user.rights.user.all' ) );
        $this->loginAs('view_user_all_user_rights');

        $this->client->request('GET', $this->client->getContainer()->get('router')->generate('user.remove', array('id'=> '1000000000' ), false));
        $this->assertFalse($this->client->getResponse()->isSuccessful());

        $this->client->request('GET', $this->client->getContainer()->get('router')->generate('user.remove', array('id'=>$userId), false));
        $this->client->request(
            'POST',
            $this->client->getContainer()->get('router')->generate('user.remove', array('id'=>$userId), false),
            array()
        );
        $this->assertTrue($this->client->getResponse()->isRedirect($this->client->getContainer()->get('router')->generate('user.index')));
    }

    public function testListUsers()
    {
        //connecting with allowed role provokes successful response
        $this->createTempUser('view_user_all_user_rights', array ('sygefor_user.rights.user.all' ) );
        $this->loginAs('view_user_all_user_rights');
        $this->client->request('GET',$this->client->getContainer()->get('router')->generate('user.index', array(), false));
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->assertContains("Liste des utilisateurs", $this->client->getResponse()->getContent()) ;

        //connecting with non allowed role provokes unsuccessful response
        $this->loginWithRoles(array('ROLE_USER'));
        $this->client->request('GET',$this->client->getContainer()->get('router')->generate('user.index', array(), false));
        $this->assertFalse($this->client->getResponse()->isSuccessful());
    }
}
