<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 31/03/14
 * Time: 10:15.
 */
namespace Sygefor\Bundle\CoreBundle\Tests\Form;

use Sygefor\Bundle\CoreBundle\Entity\User\User;
use Sygefor\Bundle\CoreBundle\Form\Type\LoginFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class LoginTypeTest  extends TypeTestCase
{
    /**
     * @dataProvider getFormData
     */
    public function testFormLogin($data)
    {
        $type = new LoginFormType();
        $form = $this->factory->create($type);
        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame($data, $form->getData());

        $view     = $form->createView();
        $children = $view->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    /**
     * @return array
     */
    public function getFormData()
    {
        return array(
            array(
                'data' => array(
                    'username' => 'user_2',
                    'password' => array('pass2'),
                ),
            ),

        );
    }
}
