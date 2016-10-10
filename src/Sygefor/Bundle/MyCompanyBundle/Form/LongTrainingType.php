<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 6/7/16
 * Time: 10:26 AM.
 */
namespace Sygefor\Bundle\MyCompanyBundle\Form;

use Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType;
use Sygefor\Bundle\TrainingBundle\Form\TrainingType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class LongTrainingType extends TrainingType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('publicTypes', EntityType::class, array(
                'label' => 'Publics prioritaires',
                'class' => PublicType::class,
                'multiple' => true,
                'required' => false,
            ))
            ->add('prerequisites', null, array(
                'label'    => 'Pré-requis',
                'required' => false,
            ))
            ->add('modules', CollectionType::class, array(
                'label'        => 'Modules',
                'type'         => ModuleType::class,
                'required'     => false,
                'allow_add'    => true,
                'allow_delete' => true,
            ));

        parent::buildForm($builder, $options);
    }
}
