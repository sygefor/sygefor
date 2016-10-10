<?php

namespace Sygefor\Bundle\CoreBundle\Serializer\Handler;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormErrorIterator;

/**
 * Class FormErrorIteratorHandler.
 */
class FormErrorIteratorHandler implements SubscribingHandlerInterface
{
    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'Symfony\\Component\\Form\\FormErrorIterator',
                'method' => 'serializeToJson',
            ),
        );
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param FormErrorIterator $formErrorIterator
     * @param array $type
     * @param SerializationContext $context
     *
     * @return mixed
     */
    public function serializeToJson(JsonSerializationVisitor $visitor, FormErrorIterator $formErrorIterator, array $type, SerializationContext $context)
    {
        return $visitor->getNavigator()->accept($this->getErrors($formErrorIterator->getForm()), array('name' => 'array'), $context);
    }

    /**
     * @param Form $form
     *
     * @return array
     */
    protected function getErrors(Form $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $key => $child) {
            if ($err = $this->getErrors($child)) {
                $errors[$key] = $err;
            }
        }

        return $errors;
    }
}
