<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 31/03/2015
 * Time: 14:57
 */

namespace Sygefor\Bundle\TraineeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ChangeDuplicateIgnoranceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('ignored', 'choice', array(
            'choices' => array(true => 'Non', false => 'Oui'),
            'multiple' => false
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'changeDuplicateIgnorance';
    }
}