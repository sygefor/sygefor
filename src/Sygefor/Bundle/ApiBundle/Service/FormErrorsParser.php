<?php

namespace Sygefor\Bundle\ApiBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\Form;

/**
 * Class FormErrorsParser.
 */
class FormErrorsParser extends ContainerAware
{
    /**
     * This is the main method of service. Pass form object and call it to get resulting array.
     *
     * @param \Symfony\Component\Form\Form $form
     *
     * @return array
     */
    public function parseErrors(Form $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            //If the message requires pluralization
            if($error->getMessagePluralization() !== null) {
                $errors[$key] = $this->container->get('translator')->transChoice(
                  $error->getMessage(),
                  $error->getMessagePluralization(),
                  $error->getMessageParameters(),
                  'validators'
                );
            }
            //Otherwise, we do a classic translation
            else {
                $errors[$key] = $this->container->get('translator')->trans(
                  $error->getMessage(),
                  array(),
                  'validators'
                );
            }
        }
        if ($form->count()) {
            foreach ($form as $child) {
                if ( ! $child->isValid()) {
                    if( ! isset($errors['fields'])) {
                        $errors['fields'] = array();
                    }
                    $_errors = $this->parseErrors($child);
                    if(count($_errors)) {
                        $errors['fields'][$child->getName()] = $_errors;
                    }
                }
            }
        }

        return $errors;
    }
}
