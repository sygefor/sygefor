<?php

namespace Sygefor\Bundle\CoreBundle\Serializer\Handler;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Form\FormView;

/**
 * Class FormViewHandler.
 */
class FormViewHandler implements SubscribingHandlerInterface
{
    protected static $baseTypes = array(
        'text', 'textarea', 'email', 'integer', 'money', 'number', 'password', 'percent', 'search', 'url', 'hidden',
        'collection', 'choice', 'checkbox', 'radio', 'datetime', 'date', 'time',
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
     *
     * @return array
     */
    public function serializeToJson(JsonSerializationVisitor $visitor, FormView $formView, array $type, SerializationContext $context)
    {
        $variables = $formView->vars;
        $element = array(
            'id' => $variables['id'],
            'name' => $variables['name'],
            'full_name' => $variables['full_name'],
            'label' => $variables['label'],
            'errors' => $variables['errors'],
            'value' => $variables['value'],
            'required' => $variables['required'],
            'attr' => $variables['attr'],
            'valid' => $variables['valid'],
        );

        foreach (array('multiple', 'expanded', 'checked', 'allow_add', 'allow_delete') as $optional) {
            if (isset($variables[$optional])) {
                $element[$optional] = $variables[$optional];
            }
        }

        // type
        foreach ($variables['block_prefixes'] as $blockPrefix) {
            if (in_array($blockPrefix, static::$baseTypes, true)) {
                $element['type'] = $blockPrefix; // We use the last found
            }
        }

        // children
        $children = array();
        foreach ($formView as $child) {
            $children[$child->vars['name']] = $this->serializeToJson($visitor, $child, $type, $context);
        }
        if ($children) {
            $element['children'] = $children;
        }

        // choices
        if (isset($variables['choices'])) {
            $expanded = !empty($variables['expanded']);
            $element['choices'] = $this->buildChoices($variables['choices'], $expanded, $variables);
            if (!$variables['required'] && (!isset($variables['multiple']) || !$variables['multiple']) && $variables['value']) {
                array_unshift($element['choices'], array(
                    'v' => null,
                    'l' => isset($variables['empty_value']) && !empty($variables['empty_value']) ? $variables['empty_value'] : 'Aucun',
                ));
            }
        }

        return $visitor->getNavigator()->accept($element, array('name' => 'array'), $context);
    }

    /**
     * Build the choices.
     *
     * @param $choices
     * @param bool $expanded
     */
    private function buildChoices($choices, $expanded, $variables)
    {
        if ($expanded) {
            $fullName = $variables['full_name'];
            $elementId = $variables['id'];

            $recursiveChoicesHandle = function ($choices) use (&$recursiveChoicesHandle, $fullName, $elementId) {
                $return = array();
                foreach ($choices as $key => $choice) {
                    $return[] = array(
                        'id' => $key,
                        'name' => $fullName . '[]',
                        'v' => $choice->value,
                        'l' => $choice->label,
                    );
                }

                return $return;
            };

            return $recursiveChoicesHandle($choices);
        }
        else {
            $recursiveChoicesHandle = function ($choices) use (&$recursiveChoicesHandle) {
                $return = array();
                foreach ($choices as $key => $choice) {
                    if (is_array($choice)) {
                        $return[] = array('v' => $key, 'l' => $recursiveChoicesHandle($choice));    // need to use a object to keep the order during JSON processing
                    }
                    else {
                        $return[] = array('v' => $choice->value, 'l' => $choice->label);
                    }
                }

                return $return;
            };

            return $recursiveChoicesHandle($choices);
        }
    }
}
