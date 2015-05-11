<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 01/04/14
 * Time: 14:10
 */
namespace Sygefor\TaxonomyBundle\Tests\Form;

use Sygefor\Bundle\TaxonomyBundle\Form\VocabularyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Authentication\Token;

class VocabularyTypeTest extends TypeTestCase{


    /**
     * @return array
     */
    protected function getExtensions()
    {
        $mockEntityType = $this->mockEntityType();
         /*$this->getMockBuilder('Symfony\Bridge\Doctrine\Form\Type\EntityType')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEntityType->expects($this->any())->method('getName')
            ->will($this->returnValue('entity'));
        */
        return array(new PreloadedExtension(array(
            $mockEntityType->getName() => $mockEntityType,
        ), array()));
    }

    /**
     * setUp
     */
    protected function setUp()
    {
        parent::setUp();

        //$validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        //$validator->expects($this->any())->method('validate')->will($this->returnValue(array()));

        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            //->addTypeExtension(new FormTypeValidatorExtension($validator))
            //->addTypeGuesser($this->getMockBuilder('Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser')->disableOriginalConstructor()->getMock())
            ->getFormFactory();

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }


    public function getFormData()
    {
        return array(
            array(
                'data'=> array(
                    'name' => 'terme A',
                ),
            ),
            array(
                'data'=> array(
                    'name' => 'terme B',
                ),
            ),
            array(
                'data'=> array(
                    'name' => 'terme C',
                ),
            ),
        );

    }

    /**
     *
     * @dataProvider getFormData
     */
    public function testFormSavesData($data)
    {
        $this->markTestSkipped("pending...");
        $user = $this->mockUser();

        $type = new VocabularyType($this->mockSecurityContext($user));
        $form = $this->factory->create($type);

        $form->submit($data);

        $this->asserEquals($data,$form->getData());

    }

    /**
     * @param $user
     * @return \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private function mockSecurityContext($user)
    {

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        $token->expects($this->any())
            ->method("isGranted")
            ->with('ADD',$this->any())
            ->will($this->returnValue(true));

        /**
         * @var \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
         */
        $securityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')->disableOriginalConstructor()->getMock();
        $securityContext->expects($this->any())
        ->method('getToken')
        ->will($this->returnValue($token));

        return $securityContext;
    }

    private function mockUser()
    {
        $organization = $this->getMock('Sygefor\Bundle\CoreBundle\Entity\Organization');
        $organization->expects($this->any())
            ->method('isNational')
            ->will($this->returnValue(true));

        $user = $this->getMock('Sygefor\Bundle\UserBundle\User');
        $user->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($organization));
    }

    private function mockEntityType()
    {
        $mockEntityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();

        $mockRegistry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->setMethods(array('getManagerForClass'))
            ->getMock();

        $mockRegistry->expects($this->any())->method('getManagerForClass')
            ->will($this->returnValue($mockEntityManager));

        $mockEntityType = $this->getMockBuilder('Symfony\Bridge\Doctrine\Form\Type\EntityType')
            ->setMethods(array('getName'))
            ->setConstructorArgs(array($mockRegistry))
            ->getMock();

        $mockEntityType->expects($this->any())->method('getName')
            ->will($this->returnValue('entity'));

        return $mockEntityType;
    }

} 