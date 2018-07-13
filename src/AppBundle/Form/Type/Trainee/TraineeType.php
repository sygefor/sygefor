<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/9/16
 * Time: 4:35 PM.
 */

namespace AppBundle\Form\Type\Trainee;

use AppBundle\Entity\Term\PublicType;
use AppBundle\Entity\Trainee\Trainee;
use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractTraineeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraineeType extends AbstractTraineeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('email', EmailType::class, array(
                'label' => 'Email',
            ))
            ->add('phoneNumber', null, array(
                'label' => 'Numéro de téléphone',
                'required' => false,
            ))
            ->add('publicType', EntityType::class, array(
                'label' => 'Type de personnel',
                'class' => PublicType::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('pt')->orderBy('pt.'.PublicType::orderBy(), 'ASC');
                },
                'required' => false,
            ))
            ->add('otherPublicType', TextType::class, array(
                'label' => 'Autre type de personnel',
                'required' => false,
            ))
            ->add('status', null, array(
                'label' => 'Statut',
                'required' => false,
            ))
            ->add('function', null, array(
                'label' => 'Fonction',
                'required' => false,
            ))
            ->add('isPaying', CheckboxType::class, array(
                'label' => 'Payant',
                'required' => false,
            ))
            ->add('isActive', CheckboxType::class, array(
                'label' => 'Validé',
                'required' => false,
            ));
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Trainee::class,
            'validation_groups' => array('Default', 'trainee'),
            'enable_security_check' => true,
        ));
    }
}
