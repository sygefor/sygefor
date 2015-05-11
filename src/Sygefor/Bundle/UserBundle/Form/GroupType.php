<?php
namespace Sygefor\Bundle\UserBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Validator\Constraints\Length;

class GroupType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //field name
        $builder
            ->add('name', 'text', array(
                'constraints' => new Length(array('min' => 3)),
                'invalid_message' => 'Le nom du groupe est trop court',
                'label' => 'Nom'
            ));

        $builder->add('rights', 'access_rights', array('label' => 'Droits d\'accès'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'groupformtype';
    }

}
