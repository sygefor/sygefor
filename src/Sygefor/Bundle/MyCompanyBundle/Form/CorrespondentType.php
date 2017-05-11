<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/9/16
 * Time: 9:55 AM
 */

namespace Sygefor\Bundle\MyCompanyBundle\Form;


use Sygefor\Bundle\MyCompanyBundle\Entity\Correspondent;
use Sygefor\Bundle\MyCompanyBundle\Entity\Institution;
use Sygefor\Bundle\CoreBundle\Form\Type\EntityHiddenType;
use Sygefor\Bundle\InstitutionBundle\Form\BaseCorrespondentType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CorrespondentType
 * @package Sygefor\Bundle\MyCompanyBundle\Form
 */
class CorrespondentType extends BaseCorrespondentType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('phoneNumber', TextType::class, array(
                'label'    => 'Téléphone',
                'required' => false,
            ))
            ->add('email', EmailType::class, array(
                'label'    => 'Email',
            ));
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => Correspondent::class,
            'validation_groups' => array('Correspondent'),
        ));
    }
}