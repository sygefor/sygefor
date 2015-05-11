<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 24/03/14
 * Time: 17:32
 */

namespace Sygefor\Bundle\UserBundle\Tests\Form;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Sygefor\Bundle\UserBundle\Entity\User;
use Sygefor\Bundle\UserBundle\Form\UserFormType;
use Sygefor\Bundle\UserBundle\Form\UserType;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;


/**
 * Class UserTypeTest
 * @package Sygefor\Bundle\UserBundle\Tests\Form
 */
class UserTypeTest extends TypeTestCase
{

    protected $emRegistry;
    protected $em;

    protected function tearDown()
    {
        parent::tearDown();

        $this->em = null;
        $this->emRegistry = null;
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $mockEntityType = $this->getMockBuilder('Symfony\Bridge\Doctrine\Form\Type\EntityType')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->createTestEntityManager();
        //$this->em->s

        $this->emRegistry = $this->createRegistryMock('default', $this->em);

        return array_merge(parent::getExtensions(), array(
            new DoctrineOrmExtension($this->emRegistry),
        ));
    }

    /**
     * setUp
     */
    protected function setUp()
    {
        parent::setUp();

        $validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        $validator->expects($this->any())->method('validate')->will($this->returnValue(array()));

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(new FormTypeValidatorExtension($validator))
            ->addTypeGuesser($this->getMockBuilder('Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser')->disableOriginalConstructor()->getMock())
            ->getFormFactory();

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    public function getContainerMock()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        return $container;
    }

    /**
     * @dataProvider getFormData
     */
    public function testFormSavesData($data)
    {
        $this->markTestSkipped('Need to fix EntityManager prior to run this test...');

        $user = new User();
        $user->setRoles(array('ROLE_ADMIN'));

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->disableOriginalConstructor()
            ->getMock()
            ->expects($this->any())->method('getUser')
            ->will($this->returnValue($user));

        $securityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->disableOriginalConstructor()
            ->getMock()
            ->expects($this->any())->method('getToken')
            ->will($this->returnValue($token));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock()
            ->expects($this->any())->method('get')
            ->will($this->returnValue($securityContext));

        $accessRightRegistry = $this->getMockBuilder('Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry', null, $container)
            ->disableOriginalConstructor()
            ->getMock()
            ->expects($this->any())->method('getSecurityContext')
            ->will($this->returnValue($securityContext));


        $type = new UserType($accessRightRegistry);
        $form = $this->factory->create($type);
        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($data, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function getFormData()
    {
        return array(
            array(
                'data'=> array(
                    'username' => 'plopiplop',
                    'email' => 'plop@mail.com',
                    'plainPassword' => array('first' => 'plopiplop',
                                        'second' => 'plopiplop'),
                    'organization' => '',
                    'enabled' => '1',
                ),),);
    }

    protected function createRegistryMock($name, $em)
    {
        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($name))
            ->will($this->returnValue($em));

        return $registry;
    }

    /**
     * Returns an entity manager for testing.
     *
     * @return EntityManager
     */
    public function createTestEntityManager()
    {
        if (!class_exists('PDO') || !in_array('sqlite', \PDO::getAvailableDrivers())) {
            \PHPUnit_Framework_TestCase::markTestSkipped('This test requires SQLite support in your environment');
        }

        $config = new Configuration();
        $config->setEntityNamespaces(array('SygeforUserBundle' => "Sygefor\\Bundle\\UserBundle\\Entity"));
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('SymfonyTests\Sygefor');
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setMetadataCacheImpl(new ArrayCache());

        $params = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        return EntityManager::create($params, $config);
    }

} 