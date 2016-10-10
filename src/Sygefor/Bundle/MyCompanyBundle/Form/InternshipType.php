<?php

namespace Sygefor\Bundle\MyCompanyBundle\Form;

use Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType;
use Sygefor\Bundle\TrainingBundle\Form\TrainingType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class InternshipType.
 */
class InternshipType extends TrainingType
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
            ));

        parent::buildForm($builder, $options);
    }
}
