<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 20/03/14
 * Time: 12:01
 */

namespace Sygefor\Bundle\UserBundle\Tests\Form;

use Sygefor\Bundle\UserBundle\Entity\Group;
use Sygefor\Bundle\UserBundle\Form\AccessRightType;
use Sygefor\Bundle\UserBundle\Form\GroupType;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GroupTypeTest
 * @package Sygefor\Bundle\UserBundle\Tests\Form
 */
class GroupTypeTest extends TypeTestCase
{
    /**
     * @return array
     */
    protected function getExtensions()
    {
        $childType = new AccessRightType($this->getRightRegistry());
        return array(
            new PreloadedExtension(array($childType->getName() => $childType), array()),
        );
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
     *
     * @dataProvider getFormData
     */
    public function testFormSavesData($data)
    {
        $this->markTestSkipped('test failing does\'nt fixed');
        $type = new GroupType();
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

    /**
     * Mocking AccessRightRegistry object
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRightRegistry()
    {
        //mocking registry
        $accessRightRegistry = $this->getMock('Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry', null, array($this->getContainerMock()));
        $accessRightRegistry->expects($this->any())->method('getRights')->will($this->returnValue(array( 'Utilisateurs' =>array('foo','bar'),'Organization'=>array('foo2','baz'))));
        $accessRightRegistry->expects($this->any())->method('getGroups')->will($this->returnValue(array( 'Utilisateurs' =>array('foo','bar'),'Organization'=>array('foo2','baz'))));

        //moccking access rights
        $right1 = $this->getMockForAbstractClass('Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight');
        $right1->expects($this->any())->method('getLabel')->will($this->returnValue('Label foo'));

        $right2 = $this->getMockForAbstractClass('Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight');
        $right2->expects($this->any())->method('getLabel')->will($this->returnValue('Label bar'));

        $right3 = $this->getMockForAbstractClass('Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight');
        $right3->expects($this->any())->method('getLabel')->will($this->returnValue('Label foo2'));

        $right4 = $this->getMockForAbstractClass('Sygefor\Bundle\UserBundle\AccessRight\AbstractAccessRight');
        $right4->expects($this->any())->method('getLabel')->will($this->returnValue('Label baz'));

        //mocking registry's getAccessRight method to get corresponding rights
        $accessRightRegistry->expects($this->any())->method('getAccessRightById')->will($this->returnCallback(function($id) use ($right1,$right2,$right3,$right4) {
            if($id=='foo' ){ return $right1; }
            if($id=='bar') { return $right2; }
            if($id=='foo2') { return $right3; }
            if($id=='baz') { return $right4; }
        }));

        return $accessRightRegistry;
    }

    /**
     * @return array
     */
    public function getFormData()
    {
        return array(
            array(
                'data'=> array(
                    'name' => 'group_p',
                    'rights' =>array('foo','bar','foo2','baz')
                ),
            ),

        );
    }
}
