<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 18/03/14
 * Time: 17:08
 */
namespace Sygefor\Bundle\UserBundle\Tests\Controller;


use Sygefor\Bundle\CoreBundle\Test\WebTestCase;
use Sygefor\Bundle\UserBundle\Controller\GroupController;
use Sygefor\Bundle\UserBundle\Entity\Group;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Client as Client;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Component\DomCrawler\Crawler;
use Doctrine\ORM\EntityManager;

/**
 * Class GroupControllerTest
 * @package Sygefor\Bundle\UserBundle\Tests\Controller
 */
class GroupControllerTest extends WebTestCase
{
    /**
     * testAddGroup
     */
    public function setUp()
    {
        parent::setUp();
        $this->createTempUser('group_admin', array('sygefor_user.rights.group'));
    }

    /**
     * testAddGroup
     */
    public function testAddGroup()
    {
        $url = $this->generateUrl('group.add');
        $this->client->request('GET', $url);
        $this->assertResponseSuccess(false);

        $this->loginAs('group_admin');
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseSuccess(true);

        $csrfToken = $this->extractCsrfToken($crawler, 'groupformtype[_token]');
        $this->postData($url, array('groupformtype' => array('name' => 'foo' . uniqid(), "_token" => $csrfToken)));
        $this->assertResponseRedirect(true);
    }

    /**
     *
     */
    public function testEditGroup()
    {
        /** @var  */
        $em = $this->getEntityManager();

        /** @var Group $group */
        $group = $em->getRepository('SygeforUserBundle:Group')->findOneBy(array());
        $url = $this->generateUrl('group.edit', array('id' => $group->getId()));

        $this->client->request('GET', $url);
        $this->assertResponseSuccess(false);

        $this->loginAs('group_admin');
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseSuccess(true);

        //posting request with name
        $csrfToken = $this->extractCsrfToken($crawler, 'groupformtype[_token]');
        $this->postData($url, array(
            'groupformtype' => array('name' => 'foo' . uniqid(), "_token" => $csrfToken)
        ));
        $this->assertResponseRedirect(true);
    }

    /**
     *
     */
    public function testRemoveGroup()
    {
        $repository = $this->getEntityManager()->getRepository('SygeforUserBundle:Group');
        $group = $repository->findOneBy(array());
        $url = $this->generateUrl('group.remove', array('id' => $group->getId()));

        $this->client->request('GET', $url);
        $this->assertResponseSuccess(false);

        $this->loginAs('group_admin');
        $this->client->request('GET', $url);
        $this->assertResponseSuccess(true);

        $this->client->request('POST', $url);
        $this->assertResponseRedirect(true, $this->generateUrl('group.index'));

        $repository->clear();
        $group = $repository->find($group->getId());
        $this->assertNull($group);
    }

    /**
     *
     */
    public function testListGroups()
    {
        $url = $this->generateUrl('group.index');

        $this->client->request('GET', $url);
        $this->assertResponseSuccess(false);

        $this->loginAs('group_admin');
        $this->client->request('GET', $url);
        $this->assertResponseSuccess(true);

        $this->assertContentContains("Liste des Groupes");
    }
}
