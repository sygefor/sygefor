<?php
namespace Sygefor\Bundle\CoreBundle\Test;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\UserBundle\Entity\Group;
use Sygefor\Bundle\UserBundle\Entity\User;
use Sygefor\Bundle\UserBundle\Entity\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class WebTestCase
 * @package Sygefor\Bundle\CoreBundle\Tests\Controller
 */
class WebTestCase extends BaseWebTestCase
{
    /**
     * @var Client;
     */
    protected $client = null;

    /**
     * @var array
     */
    protected $tempEntities = array();

    /**
     * setUp
     */
    protected function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    /**
     * tearDown
     * Delete temporary entities
     */
    protected function tearDown()
    {
        if($this->tempEntities) {
            $em = $this->getEntityManager();
            foreach($this->tempEntities as $entity) {
                try {
                    $entity = $em->merge($entity);
                    $em->remove($entity);
                } catch(EntityNotFoundException $e) {}
            }
            $em->flush();
        }
        parent::tearDown();
    }

    /**
     * generateUrl
     */
    protected function generateUrl($route, $params = array())
    {
        return $this->client->getContainer()->get('router')->generate($route, $params, false);
    }

    /**
     * createTempUser
     */
    protected function createTempUser($username, $accessRights = array(), $properties = array())
    {
        $em = $this->getEntityManager();
        $user = $em->getRepository('SygeforUserBundle:User')->findOneByUsername($username);
        if($user) {
            $em->remove($user);
            $em->flush();
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username . '@test');
        $user->setPassword($username);
        $user->setOrganization($em->getRepository('SygeforCoreBundle:Organization')->findOneBy(array()));

        if($properties) {
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach($properties as $path => $value) {
                $accessor->setValue($user, $path, $value);
            }
        }

        $group = $em->getRepository('SygeforUserBundle:Group')->findOneByName($username . '_group');
        if($group) {
            $em->remove($group);
            $em->flush();
        }
        $group = new Group($username . '_group');
        $group->setRights($accessRights);
        $user->addGroup($group);
        $this->getEntityManager()->persist($group);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
        array_push($this->tempEntities, $user, $group);

        return $user;
    }

    /**
     * loginAs
     */
    protected function loginAs($user)
    {
        if(is_string($user)) {
            $repository = $this->getEntityManager()->getRepository('SygeforUserBundle:User');
            $user = $repository->findOneByUsername($user);
        }
        $session = $this->client->getContainer()->get('session');
        $firewall = 'main';
        $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * loginWithRole
     * @param array $roles
     */
    protected function loginWithRoles(array $roles)
    {
        $session = $this->client->getContainer()->get('session');
        $firewall = 'main';
        $token = new UsernamePasswordToken('user', 'user', $firewall, $roles);
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * loginAsUser
     */
    protected function loginAsUser()
    {
        $this->loginWithRoles(array('ROLE_USER'));
    }

    /**
     * extractCsrfToken
     */
    protected function extractCsrfToken($crawler, $field)
    {
        $extract = $crawler->filter('input[name="' . $field . '"]')->extract(array('value'));
        return $extract[0];
    }

    /**
     * postData
     */
    protected function postData($uri, $data)
    {
        return $this->client->request('POST', $uri, $data);
    }

    /**
     * getRequest
     */
    protected function getRequest($url, $data = array())
    {
        return $this->client->request('GET', $url, $data);
    }

    /**
     * jsonRequest
     */
    protected function jsonRequest($method, $url, array $parameters = array())
    {
        return $this->client->request($method, $url, $parameters, array(), array(
            'HTTP_accept' => 'application/json',
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
    }

    /**
     * getContent
     */
    protected function getResponseContent()
    {
        return $this->client->getResponse()->getContent();
    }

    /**
     * getJsonContent
     */
    protected function getResponseJson()
    {
        self::assertJson($this->client->getResponse()->getContent());
        return json_decode($this->client->getResponse()->getContent());
    }

    /**
     * getEntityManager
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->client->getContainer()->get('doctrine')->getManager();
    }

    /**
     * getRepository
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getRepository($entityName)
    {
        return $this->getEntityManager()->getRepository($entityName);
    }

    /**
     * loadFixture
     */
    protected function loadFixture(FixtureInterface $fixture)
    {
        return $fixture->load($this->getEntityManager());
    }

    /**
     * assertResponseSuccess
     */
    protected function assertResponseSuccess($expected = true, $message = '')
    {
       self::assertThat($this->client->getResponse()->isSuccessful(), $expected ? self::isTrue(): self::isFalse(), $message);
    }

    /**
     * assertResponseCode
     */
    protected function assertResponseCode($code, $message = '')
    {
        self::assertEquals($code, $this->client->getResponse()->getStatusCode(), $message);
    }

    /**
     * assertResponseRedirect
     */
    protected function assertResponseRedirect($expected = true, $location = null, $message = '')
    {
       self::assertThat($this->client->getResponse()->isRedirect($location), $expected ? self::isTrue(): self::isFalse(), $message);
    }

    /**
     * assertContentContains
     */
    protected function assertContentContains($needle, $message = '', $ignoreCase = FALSE, $checkForObjectIdentity = TRUE)
    {
        self::assertContains($needle, $this->client->getResponse()->getContent(), $message, $ignoreCase, $checkForObjectIdentity);
    }

    /**
     * assertContentContains
     */
    protected function assertContentIsJson($message = '')
    {
        self::assertJson($this->client->getResponse()->getContent(), $message);
    }

    /**
     * assertResponseRedirectToLogin
     */
    protected function assertResponseRedirectToLogin($expected = true)
    {
        $location = $this->client->getContainer()->get('router')->generate('fos_user_security_login', array(), true);
        $this->assertResponseRedirect($expected, $location);
    }
}
