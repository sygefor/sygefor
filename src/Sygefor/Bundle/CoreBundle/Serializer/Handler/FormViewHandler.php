<?php
namespace Sygefor\Bundle\CoreBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\Context;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Form\FormView;

/**
 * Class FormViewHandler
 * @package Sygefor\Bundle\CoreBundle\Serializer
 */
class FormViewHandler implements SubscribingHandlerInterface
{
    protected static $baseTypes = array(
        'text', 'textarea', 'email', 'integer', 'money', 'number', 'password', 'percent', 'search', 'url', 'hidden',
        'collection', 'choice', 'checkbox', 'radio', 'datetime', 'date',
    );

    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'Symfony\Component\Form\FormView',
                'method' => 'serializeToJson',
            ),
        );
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param FormView $formView
     * @param array $type
     * @param SerializationContext $context
     * @return array
     */
    public function serializeToJson(JsonSerializationVisitor $visitor, FormView $formView, array $type, SerializationContext $context)
    {
        $variables = $formView->vars;
        $element = array(
            "id" => $variables["id"],
            "name" => $variables["name"],
            "full_name" => $variables["full_name"],
            "label" => $variables["label"],
            "errors" => $variables["errors"],
            "value" => $variables["value"],
            "required" => $variables["required"],
            "attr" => $variables["attr"],
            "valid" => $variables["valid"]
        );

        foreach(array('multiple', 'expanded', 'checked', 'allow_add', 'allow_delete') as $optional) {
            if(isset($variables[$optional])) {
                $element[$optional] = $variables[$optional];
            }
        }

        // type
        foreach ($variables['block_prefixes'] as $blockPrefix) {
            if (in_array($blockPrefix, static::$baseTypes)) {
                $element['type'] = $blockPrefix; // We use the last found
            }
        }

        // children
        $children = array();
        foreach($formView as $child) {
            $children[$child->vars["name"]] = $this->serializeToJson($visitor, $child, $type, $context);
        }
        if($children) {
            $element["children"] = $children;
        }

        // choices
        if(isset($variables["choices"])) {
            if(!empty($variables["expanded"])) {
                throw new \InvalidArgumentException("The expanded option is not compatible with the current implementation of FormView serializer");
            }
            $recursiveChoicesHandle = function($choices) use (&$recursiveChoicesHandle) {
                $return = array();
                foreach($choices as $key => $choice) {
                    if(is_array($choice)) {
                        //$return[$key] = $recursiveChoicesHandle($choice);
                        $return[] = array('v' => $key, 'l' => $recursiveChoicesHandle($choice));    // need to use a object to keep the order during JSON processing
                    } else {
                        // $return[$choice->value] = $choice->label;
                        // $return[] = array('k' => $choice->value, 'v' => $choice->label);
                        $return[] = array('v' => $choice->value, 'l' => $choice->label);
                    }
                }
                return $return;
            };
            $element["choices"] = $recursiveChoicesHandle($variables["choices"]);
        }

        return $visitor->getNavigator()->accept($element, ['name' => 'array'], $context);
    }
}
