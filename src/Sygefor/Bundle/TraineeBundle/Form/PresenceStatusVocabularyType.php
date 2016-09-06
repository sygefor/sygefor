<?php

namespace Sygefor\Bundle\TraineeBundle\Form;


use Sygefor\Bundle\TaxonomyBundle\Form\Type\VocabularyType;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus;
use Symfony\Component\Form\FormBuilderInterface;

class PresenceStatusVocabularyType extends VocabularyType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('status', 'choice', array(
            'label' => "Statut élémentaire",
            'expanded' => true,
            'multiple' => false,
            "required" => true,
            "choices" => array(
                PresenceStatus::STATUS_PRESENT => "Présent",
                PresenceStatus::STATUS_ABSENT => "Absent"
            )
        ));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'presencestatusvocabulary';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'vocabulary' ;
    }

}